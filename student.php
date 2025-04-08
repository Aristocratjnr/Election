<?php
session_start();
require 'configs/dbconnection.php';
require 'configs/session.php';

// Set proper error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check student authentication
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php'); 
    exit();
}

// Set correct timezone for Ghana
date_default_timezone_set('Africa/Accra');

$studentID = (int)$_SESSION['login_id'];
$hasVoted = false;
$currentElection = null;
$error = null;

try {
    // Fetch current or upcoming election (within 7 days)
    $stmt = $conn->prepare("
        SELECT * FROM elections 
        WHERE status = 'Ongoing' 
        OR (status = 'Scheduled' AND startDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
        ORDER BY startDate ASC
        LIMIT 1
    ");
    $stmt->execute();
    $currentElection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($currentElection) {
        // Check if student has already voted
        $stmt = $conn->prepare("
            SELECT 1 FROM votes 
            WHERE studentID = ? AND electionID = ?
        ");
        $stmt->bind_param('ii', $studentID, $currentElection['electionID']);
        $stmt->execute();
        $hasVoted = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Election check error: " . $e->getMessage());
    $error = "System temporarily unavailable. Please try again later.";
}

// Get student details
$student = [];
try {
    $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    error_log("Student fetch error: " . $e->getMessage());
}

$positions = [];
if ($currentElection && !$hasVoted) {
    try {
        // Get positions for current election
        $stmt = $conn->prepare("SELECT * FROM positions WHERE electionID = ?");
        $stmt->bind_param('i', $currentElection['electionID']);
        $stmt->execute();
        $positions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get candidates for each position
        foreach ($positions as &$position) {
            $stmt = $conn->prepare("
                SELECT c.*, s.name, s.department, s.profilePicture 
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.status = 'Approved'
                ORDER BY s.name ASC
            ");
            $stmt->execute();
            $position['candidates'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Positions fetch error: " . $e->getMessage());
        $error = "Error loading voting positions.";
    }
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote'])) {
    if (!$currentElection || $hasVoted) {
        $error = "You cannot vote at this time.";
    } else {
        try {
            // Start transaction
            $conn->query("START TRANSACTION");

            // Check for existing votes
            $checkStmt = $conn->prepare("SELECT 1 FROM votes WHERE electionID = ? AND studentID = ? LIMIT 1");
            $checkStmt->bind_param('ii', $currentElection['electionID'], $studentID);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                throw new Exception("You have already voted in this election.");
            }
            $checkStmt->close();

            // Validate selections
            $votes = [];
            foreach ($positions as $position) {
                if (!isset($_POST['position_' . $position['positionID']]) || empty($_POST['position_' . $position['positionID']])) {
                    throw new Exception("Please select a candidate for all positions.");
                }

                $selectedCandidates = $_POST['position_' . $position['positionID']];
                if (count($selectedCandidates) > $position['maxVotes']) {
                    throw new Exception("You can only select up to " . $position['maxVotes'] . " candidates for " . $position['title']);
                }

                // Remove duplicates
                $selectedCandidates = array_unique($selectedCandidates);
                
                foreach ($selectedCandidates as $candidateID) {
                    $votes[] = [
                        'electionID' => $currentElection['electionID'],
                        'candidateID' => (int)$candidateID,
                        'studentID' => $studentID
                    ];
                }
            }

            // Insert votes
            $stmt = $conn->prepare("INSERT INTO votes (electionID, candidateID, studentID, timestamp) VALUES (?, ?, ?, NOW())");
            $insertedVotes = [];

            foreach ($votes as $vote) {
                $voteKey = $vote['electionID'] . '-' . $vote['studentID'] . '-' . $vote['candidateID'];
                if (!isset($insertedVotes[$voteKey])) {
                    $stmt->bind_param('iii', $vote['electionID'], $vote['candidateID'], $vote['studentID']);
                    $stmt->execute();
                    $insertedVotes[$voteKey] = true;
                }
            }

            // Commit transaction
            $conn->query("COMMIT");
            $success = "Your vote has been successfully recorded!";
            $hasVoted = true;

            // Send notification
            $notification = "Thank you for voting in the " . htmlspecialchars($currentElection['name']) . " election";
            $stmt = $conn->prepare("
                INSERT INTO notifications 
                (user_id, user_type, title, message, type, related_election, related_candidate, is_read, created_at)
                VALUES (?, 'student', 'Vote Submitted', ?, 'vote', ?, NULL, 0, NOW())
            ");
            $stmt->bind_param('isi', $studentID, $notification, $currentElection['electionID']);
            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->query("ROLLBACK");
            $error = "Error submitting vote: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Portal - SmartVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: rgba(67, 97, 238, 0.08);
            --primary-dark: #3a56d4;
            --success: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --surface: #ffffff;
            --surface-hover: #f9fafb;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --card-hover-shadow: 0 15px 35px rgba(67, 97, 238, 0.12);
            --text: #374151;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --bg: #f3f4f6;
        }
        
        body {
            background-color: var(--bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            line-height: 1.5;
        }
        
        .voting-card {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
        }
        
        .candidate-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: auto;
            max-height: 250px; /* Set a maximum height for the cards */
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        
        
        .candidate-card.selected {
            border: 2px solid var(--primary);
            background-color: var(--primary-light);
            box-shadow: var(--card-hover-shadow);
        }
        
        .selection-check {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--primary);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s ease;
        }
        
        .candidate-card.selected .selection-check {
            opacity: 1;
            transform: scale(1);
        }
        
        .avatar-container {
            width: 60px;
            height: 60px;
            margin: 0 auto;
        }
        
        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--border);
            transition: all 0.4s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .candidate-card.selected .avatar {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
        }
        
        .department-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--surface);
            border: 1px solid var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .position-section {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .position-badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            font-size: 0.75rem;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .vote-submit-btn {
            padding: 0.5rem 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
            margin-top: 1rem;
            width: 100%;
            text-align: center;
        }
        
        
        
        .election-timer {
            background: linear-gradient(135deg, #4c6fff 0%, #6e41e2 100%);
            color: white;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.15);
        }
        
        .election-timer::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
        }
        
        .timer-countdown {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'DM Mono', monospace;
            letter-spacing: 1px;
        }
        
        .counter-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }
        
        .counter-circle i {
            font-size: 1.5rem;
        }
        
        .student-info {
            background: linear-gradient(to right, #f8fafc, #f1f5f9);
            border-radius: 12px;
            overflow: hidden;
            padding: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }
        
        .student-avatar {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        .student-details h5 {
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        
        .alert-success {
            background-color: var(--success-light);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .voted-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--success-light);
            color: var(--success);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }
        
        .voting-status {
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 6px 14px;
            border-radius: 8px;
        }
        
        .voting-active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .voting-inactive {
            background-color: rgba(107, 114, 128, 0.1);
            color: var(--text-muted);
        }
        
        .pulse-badge {
            animation: pulsate 2s infinite;
        }
        
        .progress-wave {
            height: 6px;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .progress-wave::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: wave 2s linear infinite;
        }
        
        .position-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .position-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .candidate-info {
            padding: 0.5rem;
            text-align: center;
            flex-grow: 1;
        }
        
        .candidate-name {
            font-weight: 700;
            margin-bottom: 0.2rem;
            transition: color 0.3s ease;
        }
        
        .candidate-position {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.75rem;
            margin-bottom: 0.3rem;
        }
        
        .candidate-tagline {
            color: var(--text-muted);
            font-size: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .candidate-card.selected .candidate-name {
            color: var(--primary);
        }
        
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid var(--success);
            background-color: rgba(16, 185, 129, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .success-checkmark i {
            font-size: 3rem;
            color: var(--success);
        }
        
        @keyframes pulsate {
            0% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
            }
        }
        
        @keyframes wave {
            0% {
                transform: translateX(-50%);
            }
            100% {
                transform: translateX(0%);
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-5px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
        
        @keyframes select-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0); }
            50% { box-shadow: 0 0 0 8px rgba(67, 97, 238, 0.3); }
        }
        
        @media (max-width: 768px) {
            .avatar-container {
                width: 85px;
                height: 85px;
            }
            
            .timer-countdown {
                font-size: 1.5rem;
            }
            
            .counter-circle {
                width: 50px;
                height: 50px;
            }
            
            .candidate-card {
                margin-bottom: 1rem;
            }
            
            .vote-submit-btn {
                width: 100%;
            }
            
            .col-md-6, .col-lg-4 {
                flex: 1 1 calc(50% - 0.5rem); /* Adjust width to fit two cards per row on mobile */
                max-width: calc(50% - 0.5rem);
            }
        }

        @media (max-width: 576px) {
            .col-md-6, .col-lg-4 {
                flex: 1 1 100%; /* Adjust width to fit one card per row on smaller screens */
                max-width: 100%;
            }
        }

        /* Welcome Tips Modal Styles */
        .welcome-modal {
            border-radius: 16px;
            overflow: hidden;
            border: none;
        }

        .welcome-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }

        .welcome-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            clip-path: polygon(0% 0%, 100% 0%, 50% 50%);
        }

        .welcome-body {
            padding: 2rem;
        }

        .tip-card {
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        

        .tip-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .tip-icon.blue {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .tip-icon.green {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .tip-icon.purple {
            background-color: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .tip-icon.orange {
            background-color: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }

        .welcome-illustration {
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        .gradient-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

       
        /* Animation for the welcome modal */
        @keyframes slideIn {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .welcome-modal .modal-content {
            animation: slideIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -0.5rem;
            margin-left: -0.5rem;
        }

        .col-md-6, .col-lg-4 {
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }

        .row.g-4 {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .col-md-6, .col-lg-4 {
            flex: 1 1 calc(33.333% - 0.5rem); /* Adjust width to fit three cards per row */
            max-width: calc(33.333% - 0.5rem);
        }

        .candidate-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 80%;
        }

        .horizontal-position .row {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.5rem;
            padding-bottom: 0.5rem;
            overflow-x: hidden; 
            -ms-overflow-style: none; 
            scrollbar-width: none;
        }

 
        .horizontal-position .row::-webkit-scrollbar {
            display: none;
        }

        .horizontal-position .col-md-6, .horizontal-position .col-lg-4 {
            flex: 0 0 auto;
            width: auto;
            max-width: none;
        }

        .live-results-btn {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.2) 100%);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.1);
            margin-top: 1rem;
        }
        
      
        /* Live Results Card Styles */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
        }
        
        .live-indicator {
            display: flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #fff;
            margin-right: 6px;
            animation: pulse 1.5s infinite;
        }
        
        .status-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(67, 97, 238, 0.1);
        }
        
        .candidate-result-card {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .candidate-result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
        
        .rank-badge {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .text-bronze {
            color: #cd7f32;
        }
        
        .candidate-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .candidate-avatar-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 1.5rem;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .progress {
            height: 6px;
            border-radius: 3px;
            background-color: #f3f4f6;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #4361ee 0%, #3a56d4 100%);
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .status-card {
                margin-bottom: 1rem;
            }
            
            .candidate-result-card {
                margin-bottom: 1rem;
            }
            
            .vote-count, .vote-percentage {
                font-size: 0.875rem;
            }
            
            .candidate-name {
                font-size: 0.9rem;
            }
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
    </style>
</head>
<body>
   
    <?php include 'includes/header.php'; ?><br>
   
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 col-sm-12">
                <div class="voting-card mb-4">
                    <div class="card-header bg-white py-4 px-4 border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="mb-3 mb-md-0">
                                <h2 class="mb-1 fw-bold"><i class="bi bi-card-checklist role-icon icon"></i>&nbsp;Voting Portal</h2>
                                <p class="text-muted mb-0">Cast your vote for the student leadership election  <i class="bi bi-clipboard-check department-icon icon"></i></p>
                            </div>
                            <div class="voting-status <?= $currentElection ? 'voting-active pulse-badge' : 'voting-inactive' ?>">
                                <i class="bi <?= $currentElection ? 'bi-broadcast' : 'bi-x-circle' ?> me-2"></i>
                                <?= $currentElection ? 'Election in Progress' : 'No Active Election' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Student Info -->
                        <div class="student-info d-flex align-items-center mb-4">
                            <div class="me-3">
                                <?php 
                                $profilePicPath = 'assets/img/profile/students/' . htmlspecialchars($student['profilePicture'] ?? '');
                                $defaultAvatarClass = 'student-avatar d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary';
                                
                                if (!empty($student['profilePicture']) && file_exists($profilePicPath)): ?>
                                    <img src="<?= $profilePicPath ?>" 
                                        class="student-avatar" 
                                        alt="Student Profile"
                                        onerror="this.onerror=null;this.className='<?= $defaultAvatarClass ?>';this.innerHTML='<i class=\'bi bi-person-fill fs-3\'></i>';">
                                <?php else: ?>
                                    <div class="<?= $defaultAvatarClass ?>">
                                        <i class="bi bi-person-fill fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="student-details">
                                <h5> <i class="bi bi-person-vcard profile-icon icon"></i>&nbsp;<?= htmlspecialchars($student['name'] ?? 'Student') ?></h5>
                                <div class="d-flex flex-wrap">
                                    <span class="me-3 text-muted small">
                                        <i class="bi bi-person-badge me-1"></i> 
                                        ID: <?= $studentID ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="bi bi-building-check icon"></i>
                                        Department: <?= htmlspecialchars($student['department'] ?? 'Department') ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($hasVoted): ?>
                                <div class="voted-badge ms-auto">
                                    <i class="bi bi-check2-circle me-1"></i> Voted
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status Messages -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-octagon-fill fs-4 me-2"></i>
                                    <div>
                                        <strong>Error!</strong> <?= $error ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                                    <div>
                                        <strong>Success!</strong> <?= $success ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Election Info -->
                        <?php if ($currentElection): ?>
                            <div class="election-timer mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-7 mb-3 mb-md-0">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="counter-circle me-3">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                            <h4 class="text-white mb-0"><?= htmlspecialchars($currentElection['name']) ?></h4>
                                        </div>
                                        <p class="text-white-50 mb-2">
                                            <?= date('F j, Y', strtotime($currentElection['startDate'])) ?> to <?= date('F j, Y', strtotime($currentElection['endDate'])) ?>
                                        </p>
                                        <div class="progress-wave mt-3"></div>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <div class="timer-countdown text-white mb-1" id="countdown-timer">
                                            <?= date('M j, Y', strtotime($currentElection['endDate'])) ?>
                                        </div>
                                        <p class="text-white-50 mb-0">
                                            <i class="bi bi-clock me-1"></i>
                                            Status: <?= $currentElection['status'] ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert bg-light border-0 rounded-4 p-4 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="counter-circle bg-secondary bg-opacity-10 text-secondary me-3">
                                        <i class="bi bi-calendar-x"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">    <i class="bi bi-people department-icon icon"></i>
                                        No Active Election</h5>
                                        <p class="mb-0 text-muted">There is currently no active election. Please check back later.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Replace the status card with a live results card -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-gradient-primary text-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-graph-up-arrow me-2"></i> Live Results
                                        </h5>
                                    </div>
                                    <div class="live-indicator">
                                        <span class="pulse-dot"></span> LIVE
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <!-- Election Status -->
                                    <div class="col-md-6">
                                        <div class="status-card p-3 rounded-3 bg-light h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="status-icon me-3">
                                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Election Status</h6>
                                                    <?php if ($currentElection): ?>
                                                        <?php if ($hasVoted): ?>
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check2-circle me-1"></i> You have voted
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary">
                                                                <i class="bi bi-clock-fill me-1"></i> Voting in progress
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-x-circle-fill me-1"></i> No active election
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Vote Count -->
                                    <?php if ($currentElection): ?>
                                    <div class="col-md-6">
                                        <div class="status-card p-3 rounded-3 bg-light h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="status-icon me-3">
                                                    <i class="bi bi-bar-chart-fill text-primary fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Current Ballot Count</h6>
                                                    <?php
                                                    // Get total votes for this election
                                                    $voteCountQuery = "SELECT COUNT(DISTINCT studentID) as totalVotes FROM votes WHERE electionID = ?";
                                                    $voteCountStmt = $conn->prepare($voteCountQuery);
                                                    $voteCountStmt->bind_param("i", $currentElection['electionID']);
                                                    $voteCountStmt->execute();
                                                    $voteCountResult = $voteCountStmt->get_result();
                                                    $voteCount = $voteCountResult->fetch_assoc()['totalVotes'];
                                                    $voteCountStmt->close();
                                                    
                                                    // Get total eligible voters
                                                    $eligibleVotersQuery = "SELECT COUNT(*) as totalVoters FROM students WHERE status = 'Active'";
                                                    $eligibleVotersResult = $conn->query($eligibleVotersQuery);
                                                    $eligibleVoters = $eligibleVotersResult->fetch_assoc()['totalVoters'];
                                                    
                                                    // Calculate turnout percentage
                                                    $turnoutPercentage = $eligibleVoters > 0 ? round(($voteCount / $eligibleVoters) * 100, 1) : 0;
                                                    ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="vote-count me-2">
                                                            <i class="bi bi-people-fill me-1"></i>
                                                            <?= number_format($voteCount) ?>
                                                        </div>
                                                        <div class="vote-percentage">
                                                            <i class="bi bi-percent me-1"></i>
                                                            <?= $turnoutPercentage ?>% turnout
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($currentElection): ?>
                                    <div class="mt-4">
                                        <h6 class="mb-3 d-flex align-items-center">
                                            <i class="bi bi-trophy-fill text-warning me-2"></i> Top Candidates
                                        </h6>
                                        <div class="row g-3">
                                            <?php
                                            // Get top candidates by vote count
                                            $topCandidatesQuery = "
                                                SELECT c.candidateID, s.name, s.profilePicture, COUNT(v.voteID) as voteCount
                                                FROM candidates c
                                                JOIN students s ON c.studentID = s.studentID
                                                LEFT JOIN votes v ON c.candidateID = v.candidateID AND v.electionID = ?
                                                WHERE c.status = 'Approved'
                                                GROUP BY c.candidateID
                                                ORDER BY voteCount DESC
                                                LIMIT 3
                                            ";
                                            $topCandidatesStmt = $conn->prepare($topCandidatesQuery);
                                            $topCandidatesStmt->bind_param("i", $currentElection['electionID']);
                                            $topCandidatesStmt->execute();
                                            $topCandidatesResult = $topCandidatesStmt->get_result();
                                            
                                            if ($topCandidatesResult->num_rows > 0):
                                                $rank = 1;
                                                while ($candidate = $topCandidatesResult->fetch_assoc()):
                                                    $votePercentage = $voteCount > 0 ? round(($candidate['voteCount'] / $voteCount) * 100, 1) : 0;
                                            ?>
                                                <div class="col-md-4">
                                                    <div class="candidate-result-card p-3 rounded-3 h-100">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="rank-badge me-2">
                                                                <i class="bi bi-<?= $rank === 1 ? 'trophy-fill text-warning' : ($rank === 2 ? 'medal-fill text-secondary' : 'award-fill text-bronze') ?>"></i>
                                                            </div>
                                                            <div class="candidate-name fw-bold"><?= htmlspecialchars($candidate['name']) ?></div>
                                                        </div>
                                                        <div class="d-flex align-items-center mb-3">
                                                            <div class="avatar-container me-3">
                                                                <?php 
                                                                $candidatePicPath = 'assets/img/profile/students/' . htmlspecialchars($candidate['profilePicture'] ?? '');
                                                                if (!empty($candidate['profilePicture']) && file_exists($candidatePicPath)): 
                                                                ?>
                                                                    <img src="<?= $candidatePicPath ?>" class="candidate-avatar" alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                                <?php else: ?>
                                                                    <div class="candidate-avatar-placeholder">
                                                                        <i class="bi bi-person-fill"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="vote-stats">
                                                                <div class="vote-count">
                                                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                                    <?= number_format($candidate['voteCount']) ?> votes
                                                                </div>
                                                                <div class="vote-percentage">
                                                                    <i class="bi bi-graph-up text-primary me-1"></i>
                                                                    <?= $votePercentage ?>%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-gradient-primary" role="progressbar" 
                                                                 style="width: <?= $votePercentage ?>%;" 
                                                                 aria-valuenow="<?= $votePercentage ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php 
                                                $rank++;
                                            endwhile;
                                            else:
                                            ?>
                                                <div class="col-12">
                                                    <div class="alert alert-light text-center">
                                                        <i class="bi bi-info-circle me-2"></i> No votes cast yet
                                                    </div>
                                                </div>
                                            <?php 
                                            endif;
                                            $topCandidatesStmt->close();
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Voting Form -->
                        <?php if ($currentElection && !$hasVoted): ?>
                            <form id="votingForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <?php foreach ($positions as $index => $position): ?>
                                    <?php if (!empty($position['candidates'])): ?>
                                        <div class="position-section <?= $index === 0 ? 'horizontal-position' : '' ?>">
                                            <div class="position-header">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="position-badge text-white me-3">Position</span>
                                                    <h4 class="mb-0 fw-bold"><?= htmlspecialchars($position['title']) ?></h4>
                                                </div>
                                                <p class="text-muted small mb-0"><?= htmlspecialchars($position['description'] ?? 'Select your candidate') ?></p>
                                                <?php if ($position['maxVotes'] > 1): ?>
                                                    <p class="text-muted small mt-1">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        You can select up to <?= $position['maxVotes'] ?> candidates
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="row g-4 <?= $index === 0 ? 'flex-nowrap overflow-auto' : '' ?>">
                                                <?php foreach ($position['candidates'] as $candidate): ?>
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="candidate-card" 
                                                             onclick="selectCandidate(this, <?= $position['positionID'] ?>, <?= $candidate['candidateID'] ?>, <?= $position['maxVotes'] ?>)">
                                                            <div class="text-center p-4 pt-4 pb-2">
                                                                <div class="avatar-container">
                                                                    <?php 
                                                                    $candidatePicPath = 'assets/img/profile/students/' . htmlspecialchars($candidate['profilePicture'] ?? '');
                                                                    if (!empty($candidate['profilePicture']) && file_exists($candidatePicPath)): ?>
                                                                        <img src="<?= $candidatePicPath ?>" 
                                                                             class="avatar" 
                                                                             alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                                    <?php else: ?>
                                                                        <div class="avatar bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary">
                                                                            <i class="bi bi-person fs-2"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <span class="department-badge"><?= htmlspecialchars($candidate['department']) ?></span>
                                                                </div>
                                                                
                                                                <div class="selection-check">
                                                                    <i class="bi bi-check2"></i>
                                                                </div>
                                                            </div>
                                                        
                                                            <div class="candidate-info text-center">
                                                                <h5 class="candidate-name"><?= htmlspecialchars($candidate['name']) ?></h5>
                                                                <p class="candidate-position"><?= htmlspecialchars($position['title']) ?></p>
                                                                <p class="candidate-tagline"><?= htmlspecialchars($candidate['manifesto'] ?? 'No manifesto provided') ?></p>
                                                            </div>
                                                            
                                                            <input type="checkbox" 
                                                                   name="position_<?= $position['positionID'] ?>[]" 
                                                                   value="<?= $candidate['candidateID'] ?>" 
                                                                   class="d-none">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-5 pt-3">
                                    <button type="submit" name="submit_vote" class="btn btn-primary btn-lg vote-submit-btn">
                                        <i class="bi bi-check-circle me-2"></i> Submit Your Vote
                                    </button>
                                    <p class="text-muted mt-3 small">
                                        <i class="bi bi-shield-check me-1"></i> Your vote is secure and anonymous
                                    </p>
                                </div>
                            </form>
                        <?php elseif ($hasVoted): ?>
                            <div class="text-center py-5 px-3">
                                <div class="mb-4">
                                    <div class="success-checkmark">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                                <h3 class="mb-3 fw-bold">Vote Submitted Successfully!</h3>
                                <p class="lead text-muted mb-4">Thank you for participating in the <?= htmlspecialchars($currentElection['name']) ?> election.</p>
                                
                                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                                    <a href="results.php?election=<?= $currentElection['electionID'] ?>" class="btn btn-outline-primary btn-lg px-4">
                                        <i class="bi bi-graph-up me-2"></i> View Election Results
                                    </a>
                                    <a href="index.php" class="btn btn-primary btn-lg px-4">
                                        <i class="bi bi-house me-2"></i> Return to Dashboard
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main><br><br><br>

    <!-- Welcome Tips Modal -->
    <div class="modal fade" id="welcomeTipsModal" tabindex="-1" aria-labelledby="welcomeTipsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content welcome-modal">
                <div class="welcome-header">
                    <h3 class="modal-title mb-2" id="welcomeTipsModalLabel">Welcome to the Voting Portal!</h3>
                    <p class="mb-0">Here are some tips to help you vote successfully</p>
                </div>
                <div class="welcome-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="tip-card">
                                <div class="tip-icon blue">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <h5>Select Carefully</h5>
                                <p class="text-muted">Review all candidates before making your selection. You can only vote once per position.</p>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon green">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </div>
                                <h5>Secure & Anonymous</h5>
                                <p class="text-muted">Your vote is completely anonymous and securely encrypted. No one can see how you voted.</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="tip-card">
                                <div class="tip-icon purple">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <h5>Time Limit</h5>
                                <p class="text-muted">The election ends soon! Make sure to submit your vote before the countdown timer reaches zero.</p>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon orange">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                                <h5>No Going Back</h5>
                                <p class="text-muted">Once you submit your vote, you cannot change it. Double-check your selections before submitting.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <img src="https://cdn-icons-png.flaticon.com/512/3132/3132736.png" alt="Voting Illustration" class="welcome-illustration" style="max-height: 150px;">
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn gradient-btn" data-bs-dismiss="modal">
                            <i class="bi bi-check-circle me-2"></i> Got it, let's vote!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced candidate selection with animation
        window.selectCandidate = function(element, positionID, candidateId, maxVotes) {
            const checkboxes = document.querySelectorAll(`input[name="position_${positionID}[]"]`);
            const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            
            if (maxVotes === 1) {
                // Single selection mode
                checkboxes.forEach(checkbox => {
                    const card = checkbox.closest('.candidate-card');
                    if (card !== element) {
                        card.classList.remove('selected');
                        checkbox.checked = false;
                    }
                });
                element.classList.add('selected');
                element.querySelector('input[type="checkbox"]').checked = true;
            } else {
                // Multiple selection mode
                const checkbox = element.querySelector('input[type="checkbox"]');
                if (selectedCount < maxVotes || checkbox.checked) {
                    element.classList.toggle('selected');
                    checkbox.checked = !checkbox.checked;
                } else {
                    alert(`You can only select up to ${maxVotes} candidates for this position.`);
                }
            }
            
            // Add special animation
            element.style.animation = 'select-pulse 0.8s ease';
            setTimeout(() => {
                element.style.animation = '';
            }, 800);
            
            // Update progress status
            updateVotingProgress();
        };
        
        // Update voting progress and validation status
        function updateVotingProgress() {
            const positionSections = document.querySelectorAll('.position-section');
            let allPositionsSelected = true;
            
            positionSections.forEach(section => {
                const checkboxes = section.querySelectorAll('input[type="checkbox"]');
                const hasSelection = Array.from(checkboxes).some(cb => cb.checked);
                if (!hasSelection) {
                    allPositionsSelected = false;
                }
            });
            
            // Update submit button state
            const submitBtn = document.querySelector('.vote-submit-btn');
            if (allPositionsSelected) {
                submitBtn.removeAttribute('disabled');
                submitBtn.classList.add('pulse-badge');
            } else {
                submitBtn.setAttribute('disabled', 'disabled');
                submitBtn.classList.remove('pulse-badge');
            }
        }

        // Initialize form validation
        const votingForm = document.getElementById('votingForm');
        if (votingForm) {
            votingForm.addEventListener('submit', function(event) {
                // Don't prevent default submission
                // event.preventDefault(); // Remove this line
                
                // Check if all positions have selections
                const positionSections = document.querySelectorAll('.position-section');
                let allPositionsSelected = true;
                let missingPositions = [];
                
                positionSections.forEach(section => {
                    const checkboxes = section.querySelectorAll('input[type="checkbox"]');
                    const hasSelection = Array.from(checkboxes).some(cb => cb.checked);
                    if (!hasSelection) {
                        allPositionsSelected = false;
                        const positionTitle = section.querySelector('h4').textContent.trim();
                        missingPositions.push(positionTitle);
                    }
                });
                
                if (!allPositionsSelected) {
                    event.preventDefault(); // Only prevent submission if validation fails
                    alert('Please select a candidate for all positions: ' + missingPositions.join(', '));
                    return;
                }
                
                // Confirm submission
                if (!confirm('Are you sure you want to submit your vote? This action cannot be undone.')) {
                    event.preventDefault();
                    return;
                }
                
                // Disable submit button to prevent double submission
                const submitBtn = document.querySelector('.vote-submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Submitting...';
                
                // Let the form submit normally
                return true;
            });
        }

        // Show welcome tips modal on first visit
        <?php if ($currentElection && !$hasVoted): ?>
            if (!sessionStorage.getItem('welcomeShown')) {
                var welcomeModal = new bootstrap.Modal(document.getElementById('welcomeTipsModal'));
                welcomeModal.show();
                sessionStorage.setItem('welcomeShown', 'true');
            }
        <?php endif; ?>
    });
    </script>
</body>
</html>