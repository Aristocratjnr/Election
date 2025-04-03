<?php
session_start();
require 'configs/dbconnection.php';
require 'configs/session.php';

// Check if user is logged in and has the student role
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.html'); // Redirect to login page if not logged in
    exit();
}

$studentID = (int)$_SESSION['login_id'];

// Check if student has already voted in current election
$hasVoted = false;
$currentElection = null;
$error = null; // Initialize $error to null

try {
    // Get current active election
    $stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'Ongoing' AND startDate <= NOW() AND endDate >= NOW() LIMIT 1");
    $stmt->execute();
    $currentElection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($currentElection) {
        // Check if student has already voted
        $stmt = $conn->prepare("SELECT 1 FROM votes WHERE studentID = ? AND electionID = ?");
        $stmt->bind_param('ii', $studentID, $currentElection['electionID']);
        $stmt->execute();
        $hasVoted = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Election check error: " . $e->getMessage());
    $error = "Error checking election status.";
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

// Get categories and candidates if election is active and student hasn't voted
$categories = [];
if ($currentElection && !$hasVoted) {
    try {
        // Get categories for current election
        $stmt = $conn->prepare("SELECT * FROM categories WHERE electionID = ?");
        $stmt->bind_param('i', $currentElection['electionID']);
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get candidates for each category
        foreach ($categories as &$category) {
            $stmt = $conn->prepare("
                SELECT c.*, s.name, s.department 
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.categoryID = ?
            ");
            $stmt->bind_param('i', $category['categoryID']);
            $stmt->execute();
            $category['candidates'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Categories fetch error: " . $e->getMessage());
        $error = "Error loading voting categories.";
    }
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote'])) {
    if (!$currentElection || $hasVoted) {
        $error = "You cannot vote at this time.";
    } else {
        try {
            $conn->begin_transaction();

            // Validate all categories have selections
            $votes = [];
            foreach ($categories as $category) {
                if (!isset($_POST['category_' . $category['categoryID']])) {
                    throw new Exception("Please select a candidate for all positions.");
                }

                $candidateID = (int)$_POST['category_' . $category['categoryID']];
                $votes[] = [
                    'electionID' => $currentElection['electionID'],
                    'categoryID' => $category['categoryID'],
                    'candidateID' => $candidateID,
                    'studentID' => $studentID
                ];
            }

            // Record votes
            $stmt = $conn->prepare("
                INSERT INTO votes 
                (electionID, categoryID, candidateID, studentID, `timestamp`) 
                VALUES (?, ?, ?, ?, NOW())
            ");

            foreach ($votes as $vote) {
                $stmt->bind_param('iiii',
                    $vote['electionID'],
                    $vote['categoryID'],
                    $vote['candidateID'],
                    $vote['studentID']
                );
                $stmt->execute();
            }

            $conn->commit();
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
            $conn->rollback();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            border: 1px solid var(--border);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            cursor: pointer;
            border-radius: 12px;
            background: var(--surface);
            overflow: hidden;
            position: relative;
            height: 100%;
        }
        
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(67, 97, 238, 0.3);
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
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto;
            perspective: 1000px;
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
        
        .category-section {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .category-badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            font-size: 0.75rem;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .vote-submit-btn {
            padding: 14px 36px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .vote-submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
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
        
        .category-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .category-header::after {
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
            padding: 1.5rem;
        }
        
        .candidate-name {
            font-weight: 700;
            margin-bottom: 0.3rem;
            transition: color 0.3s ease;
        }
        
        .candidate-position {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .candidate-tagline {
            color: var(--text-muted);
            font-size: 0.875rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
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
        }
    </style>
    <!-- Add Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?><br>
    
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="voting-card mb-4">
                    <div class="card-header bg-white py-4 px-4 border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="mb-3 mb-md-0">
                                <h2 class="mb-1 fw-bold">Voting Portal</h2>
                                <p class="text-muted mb-0">Cast your vote for the student leadership election</p>
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
                                <?php if (!empty($student['profile_Picture'])): ?>
                                    <img src="assets/img/profile/students/<?= htmlspecialchars($student['profile_Picture']) ?>" 
                                         class="student-avatar" 
                                         alt="Profile">
                                <?php else: ?>
                                    <div class="student-avatar d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-person-fill fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="student-details">
                                <h5><?= htmlspecialchars($student['name'] ?? 'Student') ?></h5>
                                <div class="d-flex flex-wrap">
                                    <span class="me-3 text-muted small">
                                        <i class="bi bi-person-badge me-1"></i> 
                                        ID: <?= $studentID ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="bi bi-briefcase me-1"></i>
                                        <?= htmlspecialchars($student['department'] ?? 'Department') ?>
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
                                            <h4 class="text-white mb-0"><?= htmlspecialchars($currentElection['title']) ?></h4>
                                        </div>
                                        <p class="text-white-50 mb-2"><?= htmlspecialchars($currentElection['description']) ?></p>
                                        <div class="progress-wave mt-3"></div>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <div class="timer-countdown text-white mb-1" id="countdown-timer">
                                            <?= date('M j, Y', strtotime($currentElection['end_date'])) ?>
                                        </div>
                                        <p class="text-white-50 mb-0">
                                            <i class="bi bi-clock me-1"></i>
                                            Ends at <?= date('h:i A', strtotime($currentElection['end_time'])) ?>
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
                                        <h5 class="mb-1">No Active Election</h5>
                                        <p class="mb-0 text-muted">There is currently no active election. Please check back later.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Voting Form -->
                        <?php if ($currentElection && !$hasVoted): ?>
                            <form id="votingForm" method="POST">
                                <?php foreach ($categories as $categoryIndex => $category): ?>
                                    <div class="category-section">
                                        <div class="category-header">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="category-badge text-white me-3"><?= $category['name'] ?></span>
                                                <h4 class="mb-0 fw-bold"><?= $category['description'] ?></h4>
                                            </div>
                                            <p class="text-muted small mb-0">Select one candidate for this position</p>
                                        </div>
                                        
                                        <div class="row g-4">
                                            <?php foreach ($category['candidates'] as $candidate): ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="candidate-card"
                                                         onclick="selectCandidate(this, <?= $category['categoryID'] ?>, <?= $candidate['candidateID'] ?>)">
                                                        <div class="text-center p-4 pt-4 pb-2">
                                                            <div class="avatar-container">
                                                                <?php if (!empty($candidate['photo'])): ?>
                                                                    <img src="assets/img/candidates/<?= htmlspecialchars($candidate['photo']) ?>" 
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
                                                            <p class="candidate-position"><?= htmlspecialchars($candidate['position']) ?></p>
                                                            <p class="candidate-tagline"><?= htmlspecialchars($candidate['tagline'] ?? '') ?></p>
                                                        </div>
                                                        
                                                        <input type="radio" 
                                                               name="category_<?= $category['categoryID'] ?>" 
                                                               value="<?= $candidate['candidateID'] ?>" 
                                                               class="d-none" 
                                                               required>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
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
                                <p class="lead text-muted mb-4">Thank you for participating in the <?= htmlspecialchars($currentElection['title']) ?> election.</p>
                                
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


    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced candidate selection with animation
        window.selectCandidate = function(element, categoryId, candidateId) {
            // Deselect other candidates
            document.querySelectorAll(`input[name="category_${categoryId}"]`).forEach(radio => {
                const card = radio.closest('.candidate-card');
                if (card !== element) {
                    card.classList.remove('selected');
                }
            });
            
            // Select current candidate
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
            
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
    const categoryCount = document.querySelectorAll('.category-section').length;
    let completedCategories = 0;
    
    // Check how many categories have selections
    document.querySelectorAll('.category-section').forEach(category => {
        const inputs = category.querySelectorAll('input[type="radio"]');
        const hasSelection = Array.from(inputs).some(input => input.checked);
        if (hasSelection) {
            completedCategories++;
        }
    });
    
    // Update submit button state
    const submitBtn = document.querySelector('.vote-submit-btn');
    if (completedCategories === categoryCount) {
        submitBtn.removeAttribute('disabled');
        submitBtn.classList.add('pulse-badge');
    } else {
        submitBtn.setAttribute('disabled', 'disabled');
        submitBtn.classList.remove('pulse-badge');
    }
    
    // Optional: Update progress indicator if you have one
    const progressPercentage = (completedCategories / categoryCount) * 100;
    // You could update a progress bar here
}

// Initialize form validation
const votingForm = document.getElementById('votingForm');
if (votingForm) {
    votingForm.addEventListener('submit', function(e) {
        const categoryCount = document.querySelectorAll('.category-section').length;
        let completedCategories = 0;
        
        // Validate all categories have selections
        document.querySelectorAll('.category-section').forEach(category => {
            const inputs = category.querySelectorAll('input[type="radio"]');
            const hasSelection = Array.from(inputs).some(input => input.checked);
            if (hasSelection) {
                completedCategories++;
            }
        });
        
        // Prevent submission if not all categories selected
        if (completedCategories < categoryCount) {
            e.preventDefault();
            alert('Please make a selection for each position before submitting your vote.');
            return false;
        }
        
        // Optional: Add confirmation dialog
        if (!confirm('Are you sure you want to submit your vote? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
}

// Countdown timer functionality
const countdownElement = document.getElementById('countdown-timer');
if (countdownElement) {
    // Get end date from PHP data or from the element's content
    const endDateText = countdownElement.textContent.trim();
    const endDate = new Date(endDateText);
    
    // Update countdown every second
    const countdownInterval = setInterval(function() {
        const now = new Date();
        const distance = endDate - now;
        
        // Time calculations
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        // Display the result
        if (distance > 0) {
            countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        } else {
            clearInterval(countdownInterval);
            countdownElement.innerHTML = "Election Ended";
            
            // Optionally reload the page or show a message
            document.location.reload();
        }
    }, 1000);
}

// Add hover effects for candidate cards
document.querySelectorAll('.candidate-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        const avatar = this.querySelector('.avatar');
        if (avatar) {
            avatar.style.transform = 'scale(1.05)';
        }
    });
    
    card.addEventListener('mouseleave', function() {
        const avatar = this.querySelector('.avatar');
        if (avatar) {
            avatar.style.transform = 'scale(1)';
        }
    });
});

// Initialize any tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
if (typeof bootstrap !== 'undefined') {
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Call this function on page load to set initial state
updateVotingProgress();
});
    </script>
</body>
</html>