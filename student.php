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
    // Fetch current election based on dates and status
    $stmt = $conn->prepare("
        SELECT *, 
            CASE 
                WHEN NOW() BETWEEN startDate AND endDate AND status = 'Ongoing' THEN 'active'
                ELSE 'inactive'
            END as election_state
        FROM elections 
        WHERE (status = 'Ongoing' OR status = 'Scheduled')
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
        // Fetch positions and candidates in a single query
        $positionsSql = "
            SELECT p.positionID, p.title, p.description, p.maxVotes, p.display_order,
                   c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                   s.name, s.department, s.profilePicture
            FROM positions p 
            LEFT JOIN candidates c ON p.positionID = c.positionID AND c.status = 'Approved'
            LEFT JOIN students s ON c.studentID = s.studentID
            WHERE p.electionID = ? 
            ORDER BY p.display_order, p.positionID ASC, s.name ASC
        ";
        $positionsStmt = $conn->prepare($positionsSql);
        $positionsStmt->bind_param("i", $currentElection['electionID']);
        $positionsStmt->execute();
        $result = $positionsStmt->get_result();
        $positionsStmt->close();

        // Process the results
        $positions = [];
        $seenPositions = [];
        while ($row = $result->fetch_assoc()) {
            $positionID = $row['positionID'];
            $lowerTitle = strtolower($row['title']);
            
            // Create candidate array if we have candidate data
            $candidate = null;
            if ($row['candidateID']) {
                $candidate = [
                    'candidateID' => $row['candidateID'],
                    'studentID' => $row['studentID'],
                    'photo' => $row['photo'],
                    'manifesto' => $row['manifesto'],
                    'status' => $row['status'],
                    'name' => $row['name'],
                    'department' => $row['department'],
                    'profilePicture' => $row['profilePicture']
                ];
            }

            if (!isset($seenPositions[$lowerTitle])) {
                // New position
                $position = [
                    'positionID' => $positionID,
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'maxVotes' => $row['maxVotes'],
                    'display_order' => $row['display_order'],
                    'candidates' => $candidate ? [$candidate] : []
                ];
                $positions[] = $position;
                $seenPositions[$lowerTitle] = count($positions) - 1;
            } else {
                // Existing position, add candidate if we have one
                if ($candidate) {
                    $positions[$seenPositions[$lowerTitle]]['candidates'][] = $candidate;
                }
            }
        }

        // Sort positions by display_order
        usort($positions, function($a, $b) {
            return $a['display_order'] - $b['display_order'];
        });

        // Debug positions
        foreach ($positions as $position) {
            error_log("Final Position: {$position['title']} - Candidates: " . count($position['candidates']));
        }

        // First, let's check for exact duplicates in the database
        $positionTitles = [];
        foreach ($positions as $position) {
            $positionTitles[] = $position['title'];
        }
        $duplicateTitles = array_diff_assoc($positionTitles, array_unique($positionTitles));
        if (!empty($duplicateTitles)) {
            error_log("Found duplicate titles in database: " . json_encode($duplicateTitles));
        }

        // Deduplicate positions by title (case-insensitive) and ensure proper order
        $uniquePositions = [];
        $seenPositionTitles = [];

        foreach ($positions as $position) {
            $lowerTitle = strtolower(trim($position['title']));
            if (!in_array($lowerTitle, $seenPositionTitles)) {
                $seenPositionTitles[] = $lowerTitle;
                $uniquePositions[] = $position;
            } else {
                // If we find a duplicate, merge its candidates with the existing position
                $existingIndex = array_search($lowerTitle, array_map('strtolower', array_column($uniquePositions, 'title')));
                if ($existingIndex !== false && isset($position['candidates'])) {
                    $uniquePositions[$existingIndex]['candidates'] = array_merge(
                        $uniquePositions[$existingIndex]['candidates'] ?? [],
                        $position['candidates']
                    );
                }
            }
        }

        $positions = $uniquePositions;

        error_log("Final positions after deduplication: " . json_encode($positions));

        // Get candidates for each position
        foreach ($positions as &$position) {
            if (!isset($position['candidates'])) {
            $stmt = $conn->prepare("
                SELECT c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                       s.name, s.department, s.profilePicture
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.positionID = ? 
                AND c.status = 'Approved'
                ORDER BY s.name ASC
            ");
            $stmt->bind_param('i', $position['positionID']);
            $stmt->execute();
            $position['candidates'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            }
            
           
            error_log("Position ID: {$position['positionID']} - Title: {$position['title']} - Candidate count: " . count($position['candidates']));
        }

        // First, let's check the database directly for duplicate positions
        $checkDuplicatesSql = "
            SELECT title, COUNT(*) as count 
            FROM positions 
            WHERE electionID = ? 
            GROUP BY LOWER(title) 
            HAVING count > 1
        ";
        $checkDuplicatesStmt = $conn->prepare($checkDuplicatesSql);
        $checkDuplicatesStmt->bind_param("i", $currentElection['electionID']);
        $checkDuplicatesStmt->execute();
        $duplicates = $checkDuplicatesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $checkDuplicatesStmt->close();

        if (!empty($duplicates)) {
            error_log("Found duplicate positions in database: " . json_encode($duplicates));
            // If we find duplicates, we need to clean them up
            foreach ($duplicates as $duplicate) {
                // For each duplicate title, keep the one with the lowest positionID and merge candidates
                $title = $duplicate['title'];
                
                // Get all positions with this title
                $getDuplicatesSql = "
                    SELECT positionID, title 
                    FROM positions 
                    WHERE electionID = ? 
                    AND LOWER(title) = LOWER(?)
                    ORDER BY positionID ASC
                ";
                $getDuplicatesStmt = $conn->prepare($getDuplicatesSql);
                $getDuplicatesStmt->bind_param("is", $currentElection['electionID'], $title);
                $getDuplicatesStmt->execute();
                $duplicatePositions = $getDuplicatesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $getDuplicatesStmt->close();
                
                if (count($duplicatePositions) > 1) {
                    // Keep the first one (lowest positionID)
                    $keepPositionID = $duplicatePositions[0]['positionID'];
                    
                    // Update candidates from other positions to point to the kept position
                    $updateCandidatesSql = "
                        UPDATE candidates 
                        SET positionID = ? 
                        WHERE positionID IN (
                            SELECT positionID 
                            FROM positions 
                            WHERE electionID = ? 
                            AND LOWER(title) = LOWER(?)
                            AND positionID != ?
                        )
                    ";
                    $updateCandidatesStmt = $conn->prepare($updateCandidatesSql);
                    $updateCandidatesStmt->bind_param("iisi", $keepPositionID, $currentElection['electionID'], $title, $keepPositionID);
                    $updateCandidatesStmt->execute();
                    $updateCandidatesStmt->close();
                    
                    // Delete the duplicate positions
                    $deleteDuplicatesSql = "
                        DELETE FROM positions 
                        WHERE electionID = ? 
                        AND LOWER(title) = LOWER(?) 
                        AND positionID != ?
                    ";
                    $deleteDuplicatesStmt = $conn->prepare($deleteDuplicatesSql);
                    $deleteDuplicatesStmt->bind_param("isi", $currentElection['electionID'], $title, $keepPositionID);
                    $deleteDuplicatesStmt->execute();
                    $deleteDuplicatesStmt->close();
                }
            }
        }

        // Now fetch positions again after cleanup
        $positionsSql = "
            SELECT DISTINCT p.positionID, p.title, p.description, p.maxVotes, p.display_order 
            FROM positions p 
            WHERE p.electionID = ? 
            ORDER BY p.display_order, p.positionID ASC
        ";
        $positionsStmt = $conn->prepare($positionsSql);
        $positionsStmt->bind_param("i", $currentElection['electionID']);
        $positionsStmt->execute();
        $positions = $positionsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $positionsStmt->close();

        // Get candidates for each position
        foreach ($positions as $key => $position) {
            $stmt = $conn->prepare("
                SELECT c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                       s.name, s.department, s.profilePicture
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.positionID = ? 
                AND c.status = 'Approved'
                ORDER BY s.name ASC
            ");
            $stmt->bind_param('i', $position['positionID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $candidates = $result->fetch_all(MYSQLI_ASSOC);
            $positions[$key]['candidates'] = $candidates;
            $stmt->close();
            
            // For debugging
            error_log("Position ID: {$position['positionID']} - Title: {$position['title']} - Candidate count: " . count($candidates));
        }

        // Double check for any remaining duplicates in memory and merge candidates
        $seenPositionTitles = [];
        $uniquePositions = [];
        foreach ($positions as $position) {
            $lowerTitle = strtolower($position['title']);
            if (!in_array($lowerTitle, $seenPositionTitles)) {
                $seenPositionTitles[] = $lowerTitle;
                $uniquePositions[] = $position;
            } else {
                // If we find a duplicate, merge its candidates with the existing position
                $existingIndex = array_search($lowerTitle, array_map('strtolower', array_column($uniquePositions, 'title')));
                if ($existingIndex !== false && isset($position['candidates'])) {
                    if (!isset($uniquePositions[$existingIndex]['candidates'])) {
                        $uniquePositions[$existingIndex]['candidates'] = [];
                    }
                    $uniquePositions[$existingIndex]['candidates'] = array_merge(
                        $uniquePositions[$existingIndex]['candidates'],
                        $position['candidates']
                    );
                }
            }
        }

        $positions = $uniquePositions;

        // Debug positions after final processing
        foreach ($positions as $position) {
            error_log("Final Position: {$position['title']} - Candidates: " . count($position['candidates'] ?? []));
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
           
            $hasVotedBefore = false;
            $checkStmt = $conn->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE electionID = ? AND studentID = ?");
            $checkStmt->bind_param('ii', $currentElection['electionID'], $studentID);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult && $checkResult->num_rows > 0) {
                $hasVotedBefore = ($checkResult->fetch_assoc()['vote_count'] > 0);
            }
            $checkStmt->close();
           
            if ($hasVotedBefore) {
                
                $deleteSQL = "DELETE FROM votes WHERE electionID = ? AND studentID = ?";
                $deleteStmt = $conn->prepare($deleteSQL);
                $deleteStmt->bind_param('ii', $currentElection['electionID'], $studentID);
                $deleteResult = $deleteStmt->execute();
                $deleteStmt->close();
                
                if (!$deleteResult) {
                    throw new Exception("Could not clear previous votes. Database error: " . $conn->error);
                }
                
                // Wait a moment to ensure database consistency
                usleep(500000); // 0.5 seconds
            }
            
            $conn->begin_transaction();

            // Validate selections
            $votes = [];
            foreach ($positions as $position) {
                if (!isset($_POST['vote_' . $position['positionID']]) || empty($_POST['vote_' . $position['positionID']])) {
                    throw new Exception("Please select a candidate for all positions.");
                }

                $selectedCandidate = $_POST['vote_' . $position['positionID']];
               
                $votes[] = [
                    'electionID' => $currentElection['electionID'],
                    'candidateID' => (int)$selectedCandidate,
                    'studentID' => $studentID
                ];
            }
            if (!empty($votes)) {
                // Start transaction for multiple inserts
                $conn->begin_transaction();
                
                try {
                    // Insert each vote individually
                    $simpleSQL = "INSERT INTO votes (electionID, candidateID, studentID, timestamp) VALUES (?, ?, ?, NOW())";
                    $simpleStmt = $conn->prepare($simpleSQL);
                    
                    // Process each vote
                    foreach ($votes as $vote) {
                        $simpleStmt->bind_param('iii', 
                            $vote['electionID'], 
                            $vote['candidateID'], 
                            $vote['studentID']
                        );
                        $insertResult = $simpleStmt->execute();
                        
                        if (!$insertResult) {
                            throw new Exception("Failed to record vote for candidate ID: " . $vote['candidateID'] . ". Database error: " . $conn->error);
                        }
                    }
                    
                    // Close the statement
                    $simpleStmt->close();
                    
                    // Now update the results table for each vote
                    foreach ($votes as $vote) {
                        // Check if result entry exists
                        $checkResStmt = $conn->prepare("SELECT resultID FROM results WHERE electionID = ? AND candidateID = ?");
                        $checkResStmt->bind_param('ii', $vote['electionID'], $vote['candidateID']);
                        $checkResStmt->execute();
                        $resultExists = ($checkResStmt->get_result()->num_rows > 0);
                        $checkResStmt->close();
                        
                        if ($resultExists) {
                            // Update existing result
                            $updateResStmt = $conn->prepare("
                                UPDATE results 
                                SET voteCount = voteCount + 1 
                                WHERE electionID = ? AND candidateID = ?
                            ");
                            $updateResStmt->bind_param('ii', $vote['electionID'], $vote['candidateID']);
                            $updateResStmt->execute();
                            $updateResStmt->close();
                        } else {
                            // Insert new result
                            $insertResStmt = $conn->prepare("
                                INSERT INTO results (electionID, candidateID, voteCount, percentage) 
                                VALUES (?, ?, 1, 0)
                            ");
                            $insertResStmt->bind_param('ii', $vote['electionID'], $vote['candidateID']);
                            $insertResStmt->execute();
                            $insertResStmt->close();
                        }
                    }
                    
                    // Update percentages
                    $updatePercentageSQL = "
                        UPDATE results r
                        JOIN (
                            SELECT candidateID, 
                                   (voteCount / (SELECT SUM(voteCount) FROM results WHERE electionID = ?)) * 100 as pct
                            FROM results 
                            WHERE electionID = ?
                        ) as calc ON r.candidateID = calc.candidateID
                        SET r.percentage = calc.pct
                        WHERE r.electionID = ?
                    ";
                    $updatePctStmt = $conn->prepare($updatePercentageSQL);
                    $updatePctStmt->bind_param('iii', 
                        $currentElection['electionID'], 
                        $currentElection['electionID'], 
                        $currentElection['electionID']
                    );
                    $updatePctStmt->execute();
                    $updatePctStmt->close();
                    
                    // Commit the transaction
                    $conn->commit();
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    throw $e;
                }
            } else {
                throw new Exception("No votes to record. Please select at least one candidate.");
            }
            
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
            
            try {
                $conn->query("
                    CREATE TABLE IF NOT EXISTS election_participation (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        electionID INT,
                        studentID INT,
                        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY (electionID, studentID)
                    )
                ");
                
                $trackStmt = $conn->prepare("
                    INSERT IGNORE INTO election_participation (electionID, studentID)
                    VALUES (?, ?)
                ");
                $trackStmt->bind_param('ii', $currentElection['electionID'], $studentID);
                $trackStmt->execute();
                $trackStmt->close();
            } catch (Exception $e) {
                // Not critical if this fails
                error_log("Participation tracking error: " . $e->getMessage());
            }
            
          
            $_SESSION['vote_cache_updated'] = time();
            $_SESSION['vote_success'] = true;
            $_SESSION['vote_timestamp'] = time();
            
            // Redirect to live results page after successful vote
            header("Location: live_results.php?election=" . $currentElection['electionID'] . "&vote_success=1&t=" . time());
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            try {
                $conn->rollback();
            } catch (Exception $rollbackEx) {
                // Ignore rollback errors
            }
            $error = "Error submitting vote: " . $e->getMessage();
            error_log("Vote submission error: " . $e->getMessage());
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
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#4169E1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SmartVote">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/img/favicon/apple-touch-icon.png">
    
    <!-- Existing CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/student.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Bubble Background Styles for Timer and Election Details -->
    <style>
        /* Bubble container styles */
        .bubble-background {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            z-index: 1;
            box-shadow: 0 8px 20px rgba(var(--bubble-color-rgb), 0.08);
            transition: all 0.5s ease;
        }
        
        /* Individual bubble styles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(
                circle at 30% 30%, 
                rgba(var(--bubble-color-rgb), 0.15) 0%, 
                rgba(var(--bubble-color-rgb), 0.05) 80%
            );
            backdrop-filter: blur(1px);
            animation: float var(--float-time) ease-in-out infinite alternate, 
                      glow var(--glow-time) ease-in-out infinite alternate;
            z-index: -1;
            box-shadow: inset 0 0 10px rgba(var(--bubble-color-rgb), 0.1),
                        0 0 15px rgba(var(--bubble-color-rgb), 0.05);
            opacity: var(--bubble-opacity);
        }
        
        /* Light theme bubbles */
        html:not([data-bs-theme="dark"]) .bubble-background {
            --bubble-color-rgb: 65, 105, 225; /* Royal blue color RGB */
            --bubble-gradient: linear-gradient(135deg, rgba(65, 105, 225, 0.05), rgba(100, 150, 255, 0.02));
            background: var(--bubble-gradient);
        }
        
        /* Dark theme bubbles */
        html[data-bs-theme="dark"] .bubble-background {
            --bubble-color-rgb: 100, 150, 255; /* Lighter blue color for dark theme */
            --bubble-gradient: linear-gradient(135deg, rgba(30, 40, 70, 0.6), rgba(20, 30, 60, 0.4));
            background: var(--bubble-gradient);
        }
        
        /* Election timer with bubbles */
        .election-timer.bubble-background {
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(var(--bubble-color-rgb), 0.1);
        }
        
        .election-timer.bubble-background:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(var(--bubble-color-rgb), 0.15);
        }
        
        /* Animation for floating bubbles */
        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg) scale(1);
            }
            50% {
                transform: translateY(var(--float-y)) translateX(var(--float-x)) rotate(var(--rotate)) scale(var(--float-scale));
            }
            100% {
                transform: translateY(calc(var(--float-y) * -0.5)) translateX(calc(var(--float-x) * -0.5)) rotate(calc(var(--rotate) * -0.5)) scale(calc(1 + (var(--float-scale) - 1) * -0.5));
            }
        }
        
        /* Glow animation for bubbles */
        @keyframes glow {
            0% {
                opacity: var(--bubble-opacity);
                filter: blur(var(--bubble-blur));
            }
            50% {
                opacity: calc(var(--bubble-opacity) * 1.5);
                filter: blur(calc(var(--bubble-blur) * 0.8));
            }
            100% {
                opacity: var(--bubble-opacity);
                filter: blur(var(--bubble-blur));
            }
        }
        
        /* Enhanced time units for countdown */
        .bubble-background .time-unit {
            background: rgba(var(--bubble-color-rgb), 0.12);
            padding: 0.6rem 0.9rem;
            border-radius: 0.6rem;
            backdrop-filter: blur(5px);
            box-shadow: 
                inset 0 1px 1px rgba(255, 255, 255, 0.15),
                0 4px 15px rgba(var(--bubble-color-rgb), 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(var(--bubble-color-rgb), 0.1);
        }
        
        html[data-bs-theme="dark"] .bubble-background .time-unit {
            background: rgba(50, 70, 120, 0.5);
            box-shadow: 
                inset 0 1px 1px rgba(255, 255, 255, 0.1),
                0 4px 15px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(70, 90, 140, 0.3);
        }
        
        .bubble-background .time-unit:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 
                inset 0 1px 1px rgba(255, 255, 255, 0.2),
                0 10px 25px rgba(var(--bubble-color-rgb), 0.3);
        }
        
        .bubble-background .time-unit span {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'DM Mono', monospace;
            color: rgba(var(--bubble-color-rgb), 1);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            display: block;
            line-height: 1;
        }
        
        html[data-bs-theme="dark"] .bubble-background .time-unit span {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .bubble-background .time-unit small {
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        
        /* Animation for bubble pulse */
        @keyframes pulseBubble {
            0% {
                opacity: var(--bubble-opacity);
                transform: scale(1);
                box-shadow: 0 0 0 rgba(var(--bubble-color-rgb), 0.5);
            }
            50% {
                opacity: calc(var(--bubble-opacity) * 1.3);
                transform: scale(1.05);
                box-shadow: 0 0 20px rgba(var(--bubble-color-rgb), 0.3);
            }
            100% {
                opacity: var(--bubble-opacity);
                transform: scale(1);
                box-shadow: 0 0 0 rgba(var(--bubble-color-rgb), 0.5);
            }
        }
        
        .bubble.pulse {
            animation: pulseBubble var(--pulse-time) infinite ease-in-out;
        }
        
        /* Time separator styling */
        .bubble-background .time-separator {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            color: rgba(var(--bubble-color-rgb), 0.6);
            margin: 0 0.2rem;
            opacity: 0.8;
            animation: pulseSeparator 2s infinite ease-in-out;
        }
        
        @keyframes pulseSeparator {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        
        /* Enhanced Student Avatar Styling */
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 10%;
            object-fit: cover;
            border: 2px solid;
            transition: all 0.4s ease;
            animation: avatar-glow 3s infinite alternate ease-in-out;
            transform: translateZ(0);
        }
        
        @keyframes avatar-glow {
            0% {
                box-shadow: 0 0 15px rgba(var(--bubble-color-rgb), 0.4),
                            inset 0 0 8px rgba(var(--bubble-color-rgb), 0.1);
            }
            100% {
                box-shadow: 0 0 25px rgba(var(--bubble-color-rgb), 0.6),
                            inset 0 0 12px rgba(var(--bubble-color-rgb), 0.2);
            }
        }
        
        html:not([data-bs-theme="dark"]) .student-avatar {
            --bubble-color-rgb: 65, 105, 225; /* Royal blue for light theme */
        }
        
        html[data-bs-theme="dark"] .student-avatar {
            --bubble-color-rgb: 100, 150, 255; /* Lighter blue for dark theme */
            border-color: rgba(var(--bubble-color-rgb), 0.5);
            filter: contrast(1.1) saturate(1.2) brightness(1.05);
        }
        
        .student-avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 0 30px rgba(var(--bubble-color-rgb), 0.7),
                        inset 0 0 15px rgba(var(--bubble-color-rgb), 0.2);
        }
        
        /* Default avatar icon styling */
        .student-avatar.d-flex {
            background: linear-gradient(135deg, 
                rgba(var(--bubble-color-rgb), 0.15) 0%, 
                rgba(var(--bubble-color-rgb), 0.3) 100%);
        }
        
        /* Candidate avatars with same effect */
        .candidate-avatar {
            border-radius: 50%;
            width: 55px;
            height: 55px;
            object-fit: cover;
            border: 2px solid rgba(var(--bubble-color-rgb), 0.3);
            box-shadow: 0 0 10px rgba(var(--bubble-color-rgb), 0.3);
            filter: contrast(1.05);
            transition: all 0.3s ease;
        }
        
        .candidate-card:hover .candidate-avatar {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(var(--bubble-color-rgb), 0.5);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?><br>
      <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10 col-sm-12">
                <div class="voting-card mb-4">
                    <div class="card-header py-4 px-4 border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="mb-3 mb-md-0">
                                <h2 class="mb-0">
                                    <i class="bi bi-card-checklist role-icon icon"></i>&nbsp;Voting Portal
                                </h2>
                                <p class="text-muted mb-0">Student Leadership Election System</p>
                            </div>
                            <div class="voting-status <?= isset($currentElection['election_state']) && $currentElection['election_state'] === 'active' ? 'voting-active pulse-badge' : 'voting-inactive' ?>">
                                <i class="bi <?= isset($currentElection['election_state']) && $currentElection['election_state'] === 'active' ? 'bi-broadcast' : 'bi-x-circle' ?> me-2"></i>
                                <?= isset($currentElection['election_state']) && $currentElection['election_state'] === 'active' ? 'Election in Progress' : 'No Active Election' ?>
                            </div>
                        </div>
                    </div>                    <div class="card-body p-4">
                        <!-- Student Info - Always visible -->
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
                                <h5><i class="bi bi-person-vcard profile-icon icon"></i>&nbsp;<?= htmlspecialchars($student['name'] ?? 'Student') ?></h5>
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-person-badge me-1"></i> 
                                    ID: <?= $studentID ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-building-check icon icon"></i>
                                    Department: <?= htmlspecialchars($student['department'] ?? 'Department') ?>
                                </div>
                            </div>
                            <?php if ($hasVoted): ?>
                                <div class="voted-badge ms-auto">
                                    <i class="bi bi-check2-circle me-1"></i> Voted
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!isset($currentElection['election_state']) || $currentElection['election_state'] !== 'active'): ?>
                            <div class="text-center p-5">
                                <div class="display-1 text-muted mb-4">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <p class="text-muted mb-4">
                                    <?php if ($currentElection): ?>
                                        Next election scheduled for: <?= date('F j, Y', strtotime($currentElection['startDate'])) ?><br>
                                        Check back then to cast your vote.
                                    <?php else: ?>
                                        There is currently no ongoing or scheduled election.<br>
                                        Please check back later or contact the administrator for more information.
                                    <?php endif; ?>
                                </p>
                                <div class="mt-4">
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="bi bi-house-door me-2"></i>Return to Home
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Active election content -->
                            <div class="election-active-content">
                                <?php if ($currentElection && $currentElection['status'] === 'Ongoing'): ?>
                                    <div class="election-timer bubble-background mb-4">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="counter-circle text-muted">
                                                    <i class="bi bi-stopwatch-fill"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h6 class="mb-2 text-muted "><i class="bi bi-calendar-event me-1"></i>Time Remaining:</h6>
                                                <div class="timer-countdown" id="election-countdown">
                                                    <div class="d-flex align-items-center justify-content-start countdown-container">
                                                        <div class="time-unit">
                                                            <span id="days">00</span>
                                                            <small>days</small>
                                                        </div>
                                                        <div class="time-separator ">:</div>
                                                        <div class="time-unit">
                                                            <span id="hours">00</span>
                                                            <small>hours</small>
                                                        </div>
                                                        <div class="time-separator ">:</div>
                                                        <div class="time-unit">
                                                            <span id="minutes">00</span>
                                                            <small>minutes</small>
                                                        </div>
                                                        <div class="time-separator ">:</div>
                                                        <div class="time-unit">
                                                            <span id="seconds">00</span>
                                                            <small>seconds</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="election-date mt-2 mb-0  alight-item-center justify-content-center"><i class="bi bi-calendar-event me-1"></i>Ends on: <?= date('F j, Y', strtotime($currentElection['endDate'])) ?></p>
                                            </div>
                                        </div>
                                    </div>                                <?php endif; ?>

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
                                    <div class="election-timer bubble-background mb-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-7 mb-3 mb-md-0">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="counter-circle me-3 text-muted">
                                                        <i class="bi bi-calendar-event"></i>
                                                    </div>
                                                    <h4 class="election-title mb-0"><?= htmlspecialchars($currentElection['name']) ?></h4>
                                                </div>
                                                <p class="election-dates mb-2">
                                                    <?= date('F j, Y', strtotime($currentElection['startDate'])) ?> to <?= date('F j, Y', strtotime($currentElection['endDate'])) ?>
                                                </p>
                                                <div class="progress-wave mt-3"></div>
                                            </div>
                                            <div class="col-md-5 text-md-end" >
                                                <div class="timer-countdown text-white-20 mb-1 text-muted" id="countdown-timer">
                                                    <?= date('M j, Y', strtotime($currentElection['endDate'])) ?>
                                                </div>
                                                <p class="election-status mb-0">
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
                                <?php if ($currentElection): ?>
                                <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div class="card-header bg-gradient-primary text-white py-3 px-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0 fw-bold text-white">Election Results</h5>
                                                <p class="mb-0 opacity-75 small text-white">Live updates from the voting system</p>
                                            </div>
                                            <div class="live-indicator">
                                                <span class="pulse-dot"></span> LIVE
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <?php
                                        // Get total votes for this election
                                        $voteCountQuery = "SELECT COUNT(DISTINCT studentID) as totalVotes FROM votes WHERE electionID = ?";
                                        $voteCountStmt = $conn->prepare($voteCountQuery);
                                        $voteCountStmt->bind_param("i", $currentElection['electionID']);
                                        $voteCountStmt->execute();
                                        $voteCountResult = $voteCountStmt->get_result();
                                        $voteCount = $voteCountResult->fetch_assoc()['totalVotes'];
                                        $voteCountStmt->close();
                                        ?>
                                        <div class="mt-4">
                                            <div class="results-section-header">
                                                <div class="results-icon">
                                                    <i class="bi bi-trophy"></i>
                                                </div>
                                                <h6>Top Candidates</h6>
                                            </div>
                                            <div class="row g-3">
                                                <?php
                                            
                                                $topCandidatesQuery = "
                                                    SELECT c.candidateID, c.photo, s.name, s.profilePicture, 
                                                           p.title as position, COUNT(v.voteID) as voteCount
                                                    FROM candidates c
                                                    JOIN students s ON c.studentID = s.studentID
                                                    JOIN positions p ON c.positionID = p.positionID
                                                    LEFT JOIN votes v ON c.candidateID = v.candidateID AND v.electionID = ?
                                                    WHERE c.status = 'Approved'
                                                    AND p.electionID = ?
                                                    GROUP BY c.candidateID
                                                    ORDER BY voteCount DESC
                                                    LIMIT 3
                                                ";
                                                $topCandidatesStmt = $conn->prepare($topCandidatesQuery);
                                                $topCandidatesStmt->bind_param("ii", $currentElection['electionID'], $currentElection['electionID']);
                                                $topCandidatesStmt->execute();
                                                $topCandidatesResult = $topCandidatesStmt->get_result();
                                                
                                                if ($topCandidatesResult->num_rows > 0):
                                                    $rank = 1;
                                                    $rankClass = ['text-gold', 'text-silver', 'text-bronze'];
                                                    $rankIcon = ['trophy', 'award', 'award'];
                                                    while ($candidate = $topCandidatesResult->fetch_assoc()):
                                                        $votePercentage = $voteCount > 0 ? round(($candidate['voteCount'] / $voteCount) * 100, 1) : 0;
                                                        $colorIndex = $rank - 1;
                                                ?>
                                                    <div class="col-md-4">
                                                        <div class="candidate-result-card">
                                                            <div class="candidate-info">
                                                                <div class="candidate-header">
                                                                    <div class="rank-badge">
                                                                        <i class="bi bi-<?= $rankIcon[$colorIndex] ?> <?= $rankClass[$colorIndex] ?>"></i>
                                                                    </div>
                                                                    <span class="candidate-position"><?= htmlspecialchars($candidate['position'] ?? 'Candidate') ?></span>
                                                                </div>
                                                                <div class="candidate-main">
                                                                    <?php 
                                                                  
                                                                    $candidateCustPhotoPath = 'uploads/candidates/' . htmlspecialchars($candidate['photo'] ?? '');
                                                                    $candidateStdPhotoPath = 'assets/img/profile/students/' . htmlspecialchars($candidate['profilePicture'] ?? '');
                                                                    
                                                                    if (!empty($candidate['photo']) && file_exists($candidateCustPhotoPath)): ?>
                                                                        <img src="<?= $candidateCustPhotoPath ?>" class="candidate-avatar" alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                                    <?php elseif (!empty($candidate['profilePicture']) && file_exists($candidateStdPhotoPath)): ?>
                                                                        <img src="<?= $candidateStdPhotoPath ?>" class="candidate-avatar" alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                                    <?php else: ?>
                                                                        <div class="avatar bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary">
                                                                            <i class="bi bi-person fs-2"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="candidate-details">
                                                                        <h6 class="candidate-name"><?= htmlspecialchars($candidate['name']) ?></h6>
                                                                        <div class="d-flex flex-column gap-2">
                                                                            <div class="vote-stats">
                                                                                <i class="bi bi-check-circle-fill text-success"></i>
                                                                                <span class="vote-count"><?= number_format($candidate['voteCount']) ?> votes</span>
                                                                            </div>
                                                                            <div class="vote-stats">
                                                                                <i class="bi bi-bar-chart-fill text-primary"></i>
                                                                                <span class="vote-percentage"><?= $votePercentage ?>% of votes</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="progress">
                                                                    <div class="progress-bar" role="progressbar" 
                                                                         style="width: <?= $votePercentage ?>%;" 
                                                                         aria-valuenow="<?= $votePercentage ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php 
                                                    $rank++;
                                                    endwhile;
                                                else:
                                                ?>
                                                    <div class="col-12">
                                                        <div class="alert alert-light border-0 shadow-sm text-center py-4">
                                                            <i class="bi bi-bar-chart text-primary fs-3 mb-3"></i>
                                                            <p class="mb-0">No votes have been cast yet. Results will appear here once voting begins.</p>
                                                        </div>
                                                    </div>
                                                <?php 
                                                endif;
                                                $topCandidatesStmt->close();
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Voting Form -->
<?php if ($currentElection && !$hasVoted): ?>
    <form id="votingForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <?php foreach ($positions as $index => $position): ?>
            <?php if (!empty($position['candidates'])): ?>
                <div class="position-section mb-5">
                    <div class="position-header mb-4 px-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="position-badge text-white me-3">Position <?= $index + 1 ?></span>
                            <h3 class="mb-0 fw-bold text-primary"><?= htmlspecialchars($position['title']) ?></h3>
                        </div>
                        <p class="text-muted mb-2"><?= htmlspecialchars($position['description'] ?? 'Select your preferred candidate') ?></p>
                        
                        <!-- Fixed vote limit display -->
                        <?php if ((int)$position['maxVotes'] > 1): ?>
                            <div class="d-flex align-items-center mt-2">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-info-circle me-1"></i>
                                    You can select up to <?= (int)$position['maxVotes'] ?> candidates
                                </span>
                                <span class="ms-2 small text-muted">
                                    (<?= count($position['candidates']) ?> candidates available)
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center mt-2">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Select one candidate
                                </span>
                                <span class="ms-2 small text-muted">
                                    (<?= count($position['candidates']) ?> candidates available)
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row g-4">
                        <?php foreach ($position['candidates'] as $candidate): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="candidate-card mb-3 position-relative">
                                    <div class="form-check">
                                        <input class="form-check-input position-absolute" type="radio" 
                                               name="vote_<?= $position['positionID'] ?>" 
                                               value="<?= $candidate['candidateID'] ?>" 
                                               id="candidate_<?= $candidate['candidateID'] ?>"
                                               required>
                                        <label class="form-check-label w-100" for="candidate_<?= $candidate['candidateID'] ?>">
                                            <div class="candidate-info">
                                                <div class="candidate-main mb-3">
                                                    <?php 
                                                    $candidatePhotoPath = 'uploads/candidates/' . htmlspecialchars($candidate['photo'] ?? '');
                                                    $studentPhotoPath = 'assets/img/profile/students/' . htmlspecialchars($candidate['profilePicture'] ?? '');
                                                    
                                                    if (!empty($candidate['photo']) && file_exists($candidatePhotoPath)): ?>
                                                        <img src="<?= $candidatePhotoPath ?>" class="candidate-avatar" alt="Candidate Photo">
                                                    <?php elseif (!empty($candidate['profilePicture']) && file_exists($studentPhotoPath)): ?>
                                                        <img src="<?= $studentPhotoPath ?>" class="candidate-avatar" alt="Student Photo">
                                                    <?php else: ?>
                                                        <div class="candidate-avatar-placeholder">
                                                            <i class="bi bi-person"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="candidate-details">
                                                        <h5 class="candidate-name"><?= htmlspecialchars($candidate['name']) ?></h5>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-2">
                                                            <?= htmlspecialchars($position['title']) ?>
                                                        </span>
                                                        <?php if (!empty($candidate['department'])): ?>
                                                            <div class="department-badge">
                                                                <i class="bi bi-buildings department-icon icon"></i>
                                                                <?= htmlspecialchars($candidate['department']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($candidate['manifesto'])): ?>
                                                    <?php 
                                                    $manifestoPath = 'uploads/manifestos/' . $candidate['manifesto'];
                                                    $fileExtension = strtolower(pathinfo($manifestoPath, PATHINFO_EXTENSION));
                                                    ?>
                                                    <div class="manifesto-btn p-2 rounded text-center" 
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#manifestoModal" 
                                                         data-manifesto="<?= htmlspecialchars($candidate['manifesto']) ?>"
                                                         data-file-type="<?= htmlspecialchars($fileExtension) ?>">
                                                        <i class="bi bi-file-text me-1"></i>
                                                        View Manifesto
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="selection-check">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <div class="sticky-bottom py-4 border-top mt-5 shadow-sm">
            <div class="container">
                <div class="text-center">
                    <button type="button" id="voteBtn" class="btn btn-primary btn-lg px-5 py-3 vote-submit-btn shadow">
                        <i class="bi bi-check-circle me-2"></i> Submit Your Vote
                    </button>
                    <p class="text-muted mt-3 small">
                        <i class="bi bi-shield-check me-1"></i> Your vote is secure and anonymous
                    </p>
                </div>
            </div>
        </div>
    </form>
<?php elseif ($hasVoted): ?>
    <!-- Success state remains the same -->
<?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main><br><br><br>

    <!-- Welcome Tips Modal -->
    <div class="modal fade" id="welcomeTipsModal" tabindex="-1" aria-labelledby="welcomeTipsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
            <div class="modal-content welcome-modal">
                <div class="welcome-header">
                    <h3 class="modal-title mb-2" id="welcomeTipsModalLabel">Welcome to the Voting Portal!</h3>
                    <p class="mb-0">Here are some tips to help you vote successfully</p>
                </div>
                <div class="welcome-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="tip-card">
                                <div class="tip-icon blue">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="tip-content">
                                    <h5>Select Carefully</h5>
                                    <p class="text-muted mb-0">Review all candidates before making your selection. You can only vote once per position.</p>
                                </div>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon green">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <div class="tip-content">
                                    <h5>Secure & Anonymous</h5>
                                    <p class="text-muted mb-0">Your vote is completely anonymous and securely encrypted. No one can see how you voted.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="tip-card">
                                <div class="tip-icon purple">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="tip-content">
                                    <h5>Time Limit</h5>
                                    <p class="text-muted mb-0">The election ends soon! Make sure to submit your vote before the countdown timer reaches zero.</p>
                                </div>
                            </div>
                            
                            <div class="tip-card">
                                <div class="tip-icon orange">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="tip-content">
                                    <h5>No Going Back</h5>
                                    <p class="text-muted mb-0">Once you submit your vote, you cannot change it. Double-check your selections before submitting.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <img src="assets/img/voting-illustration.svg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3132/3132736.png'" alt="Voting Illustration" class="welcome-illustration" style="max-height: 150px;">
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-get-started" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-right-circle me-2"></i> Got it, let's vote!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Artwork Modal -->
    <div class="modal fade" id="artworkModal" tabindex="-1" aria-labelledby="artworkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
            <div class="modal-content welcome-modal">
                <div class="welcome-header">
                    <h3 class="modal-title mb-2" id="artworkModalLabel">Artwork Gallery</h3>
                    <p class="mb-0">Showcase of election-related artwork</p>
                </div>
                <div class="modal-body text-center py-5">
                    <div id="artworkCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://cdn-icons-png.flaticon.com/512/3132/3132736.png" class="d-block mx-auto img-fluid" style="max-height: 300px;" alt="Voting Art">
                                <div class="mt-3">
                                    <h5>Democracy in Action</h5>
                                    <p class="text-muted">Make your voice heard through voting</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="assets/img/voting-illustration.png" class="d-block mx-auto img-fluid" style="max-height: 300px;" alt="Election Art" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2633/2633824.png'">
                                <div class="mt-3">
                                    <h5>Election Day</h5>
                                    <p class="text-muted">Every vote matters in our democracy</p>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="assets/img/ballot-illustration.png" class="d-block mx-auto img-fluid" style="max-height: 300px;" alt="Ballot Art" onerror="this.src='https://cdn-icons-png.flaticon.com/512/1973/1973586.png'">
                                <div class="mt-3">
                                    <h5>Fair Elections</h5>
                                    <p class="text-muted">Transparent and secure voting process</p>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#artworkCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#artworkCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn gradient-btn" data-bs-dismiss="modal">Close Gallery</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vote Confirmation Modal -->
    <div class="modal fade" id="voteConfirmationModal" tabindex="-1" aria-labelledby="voteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="voteConfirmationModalLabel"><i class="bi bi-check2-circle me-2"></i>Confirm Your Vote</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <div class="d-flex">
                            <div class="me-2">
                                <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Important Notice</h5>
                                <p class="mb-0">Once submitted, your vote cannot be changed. Please review your choices before confirming.</p>
                            </div>
                        </div>
                    </div>
                    
                    <p><strong>You are about to vote in:</strong> <?= htmlspecialchars($currentElection['name'] ?? 'Current Election') ?></p>
                    
                    <div id="voteReviewSummary">
                        <!-- Vote summary will be populated via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i> Go Back
                    </button>
                    <button type="button" class="btn btn-primary" id="finalSubmitBtn">
                        <i class="bi bi-check2-circle me-1"></i> Confirm and Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manifesto Modal -->
    <div class="modal fade" id="manifestoModal" tabindex="-1" aria-labelledby="manifestoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="manifestoModalLabel">Candidate Manifesto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="manifesto-content p-3"></div>
                    <div class="download-options text-center mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Audio element for notification sound -->
    <audio id="notification-sound" preload="auto">
        <source src="assets/audio/sounds/notification.mp3" type="audio/mpeg">
        <source src="assets/audio/sounds/notifications.mp3" type="audio/mpeg">
    </audio>    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/Election/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered: ', registration);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
    </script>
    
    <script>
         document.addEventListener('DOMContentLoaded', function() {
            // Only initialize election-related features if there's an active election
            <?php if ($currentElection): ?>
                startCountdown();
                // ...existing code...
            <?php endif; ?>

            // Initialize theme from localStorage
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', currentTheme);
            
            // Apply dark mode to header on load if needed
            if (currentTheme === 'dark') {
                const header = document.getElementById('header');
                if (header) {
                    header.classList.remove('bg-white');
                    header.classList.add('bg-dark');
                }
            }
            
            // Listen for theme change events from header
            document.addEventListener('themeChanged', function(e) {
                document.documentElement.setAttribute('data-bs-theme', e.detail.theme);
                
                // Update any theme-specific elements that might not automatically update
                const header = document.getElementById('header');
                if (header) {
                    if (e.detail.theme === 'dark') {
                        header.classList.remove('bg-white');
                        header.classList.add('bg-dark');
                    } else {
                        header.classList.remove('bg-dark');
                        header.classList.add('bg-white');
                    }
                }
            });
            
            // Add click handlers to candidate cards
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't process clicks on links or buttons
                    if (e.target.closest('a') || e.target.closest('button')) {
                        return;
                    }
                    
                    const radioButton = this.querySelector('input[type="radio"]');
                    
                    // Toggle radio button
                    radioButton.checked = true;
                    
                    // Toggle selected class
                    document.querySelectorAll('.candidate-card').forEach(card => card.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });
            
            // Handle vote button click
            const voteBtn = document.getElementById('voteBtn');
            if (voteBtn) {
                voteBtn.addEventListener('click', function() {
                    // Validate selections
                    const positions = document.querySelectorAll('.position-section');
                    let isValid = true;
                    let missingPositions = [];
                    
                    positions.forEach(position => {
                        const radioButtons = position.querySelectorAll('input[type="radio"]');
                        if (radioButtons.length > 0) {  // Only validate if position has candidates
                            const positionId = radioButtons[0].name.split('_')[1];
                            const selectedCandidate = position.querySelector(`input[name="vote_${positionId}"]:checked`);
                            
                            if (!selectedCandidate) {
                                isValid = false;
                                const positionTitle = position.querySelector('h3').textContent.trim();
                                missingPositions.push(positionTitle);
                            }
                        }
                    });
                    
                    if (!isValid) {
                        // Show error alert
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.setAttribute('role', 'alert');
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle-fill"></i> Please select candidates for the following positions: ${missingPositions.join(', ')}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        
                        const votingCard = document.querySelector('.voting-card');
                        if (votingCard) {
                            if (votingCard.querySelector('.alert')) {
                                votingCard.querySelector('.alert').remove();
                            }
                            votingCard.querySelector('.card-body').prepend(alertDiv);
                        }
                        return;
                    }
                    
                    // Show confirmation modal
                    const confirmationModal = new bootstrap.Modal(document.getElementById('voteConfirmationModal'));
                    confirmationModal.show();
                    
                    // Update vote summary
                    const summaryDiv = document.getElementById('voteReviewSummary');
                    if (summaryDiv) {
                        let summaryHTML = '<div class="list-group">';
                        positions.forEach(position => {
                            const positionTitle = position.querySelector('h3').textContent;
                            const selectedCandidate = position.querySelector('input[type="radio"]:checked');
                            
                            if (selectedCandidate) {
                                const candidateCard = selectedCandidate.closest('.candidate-card');
                                const candidateName = candidateCard.querySelector('.candidate-name').textContent;
                                summaryHTML += `<div class="list-group-item">
                                    <h6 class="mb-1">${positionTitle}</h6>
                                    <p class="mb-0">${candidateName}</p>
                                </div>`;
                            }
                        });
                        summaryHTML += '</div>';
                        summaryDiv.innerHTML = summaryHTML;
                    }
                });
            }
            
            // Handle final submission
            const finalSubmitBtn = document.getElementById('finalSubmitBtn');
            if (finalSubmitBtn) {
                finalSubmitBtn.addEventListener('click', function() {
                    const form = document.getElementById('votingForm');
                    if (form) {
                        // Add submit_vote parameter
                        const submitInput = document.createElement('input');
                        submitInput.type = 'hidden';
                        submitInput.name = 'submit_vote';
                        submitInput.value = '1';
                        form.appendChild(submitInput);
                        
                        // Show loading state
                        this.disabled = true;
                        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
                        
                        // Submit the form
                        form.submit();
                    }
                });
            }

            // Handle form submission
            const votingForm = document.getElementById('votingForm');
            if (votingForm) {
                votingForm.addEventListener('submit', function(e) {
                    // Always prevent default submission - we'll handle it through the finalSubmitBtn
                    e.preventDefault();
                    
                    // Validate selections
                    const positions = document.querySelectorAll('.position-section');
                    let isValid = true;
                    let missingPositions = [];
                    
                    positions.forEach(position => {
                        const radioButtons = position.querySelectorAll('input[type="radio"]');
                        if (radioButtons.length > 0) {  // Only validate if position has candidates
                            const positionId = radioButtons[0].name.split('_')[1];
                            const selectedCandidate = position.querySelector(`input[name="vote_${positionId}"]:checked`);
                            
                            if (!selectedCandidate) {
                                isValid = false;
                                const positionTitle = position.querySelector('h3').textContent.trim();
                                missingPositions.push(positionTitle);
                            }
                        }
                    });
                    
                    if (!isValid) {
                        // Show error alert
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.setAttribute('role', 'alert');
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle-fill"></i> Please select candidates for the following positions: ${missingPositions.join(', ')}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        
                        const votingCard = document.querySelector('.voting-card');
                        if (votingCard) {
                            if (votingCard.querySelector('.alert')) {
                                votingCard.querySelector('.alert').remove();
                            }
                            votingCard.querySelector('.card-body').prepend(alertDiv);
                        }
                        return;
                    }
                    
                    // Show confirmation modal
                    const confirmationModal = new bootstrap.Modal(document.getElementById('voteConfirmationModal'));
                    confirmationModal.show();
                });
            }

            // Handle manifesto modal
            const manifestoModal = document.getElementById('manifestoModal');
            if (manifestoModal) {
                manifestoModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const manifestoFile = button.getAttribute('data-manifesto');
                    const fileType = button.getAttribute('data-file-type');
                    const modalBody = manifestoModal.querySelector('.manifesto-content');
                    const downloadOptions = manifestoModal.querySelector('.download-options');
                    
                    // Construct proper file paths
                    const baseUrl = window.location.origin;
                    const manifestoPath = `${baseUrl}/Election/uploads/manifestos/${manifestoFile}`;
                    const localPath = `uploads/manifestos/${manifestoFile}`;
                    
                    // Show loading state
                    modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading document preview...</p></div>';
                    
                    if (fileType === 'pdf') {
                        // Try object tag first with fallback to iframe
                        modalBody.innerHTML = `
                            <object data="${localPath}" type="application/pdf" width="100%" height="75vh" class="pdf-viewer">
                                <iframe src="${localPath}" width="100%" height="75vh" class="pdf-fallback" style="display:none;">
                                    <p>This browser does not support PDF preview. 
                                    <a href="${localPath}" download>Download the PDF</a> to view it.</p>
                                </iframe>
                            </object>
                        `;
                        
                        // Check if PDF viewer failed and show fallback
                        setTimeout(() => {
                            const object = modalBody.querySelector('object');
                            const iframe = modalBody.querySelector('iframe');
                            if (object && object.getBoundingClientRect().height === 0) {
                                object.style.display = 'none';
                                iframe.style.display = 'block';
                            }
                        }, 1000);
                        
                        downloadOptions.innerHTML = `
                            <div class="btn-group">
                                <a href="${localPath}" class="btn btn-primary" download>
                                    <i class="bi bi-download"></i> Download PDF
                                </a>
                                <a href="${localPath}" class="btn btn-outline-primary" target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                </a>
                            </div>
                        `;
                    } else if (fileType === 'docx') {
                        // Use Office Online Viewer with proper URL encoding
                        const encodedUrl = encodeURIComponent(manifestoPath);
                        const officeViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodedUrl}`;
                        
                        modalBody.innerHTML = `
                            <div class="docx-preview-container">
                                <iframe src="${officeViewerUrl}" width="100%" height="75vh" frameborder="0">
                                    This is an embedded <a target="_blank" href="${officeViewerUrl}">Microsoft Office</a> document.
                                </iframe>
                            </div>
                        `;
                        
                        downloadOptions.innerHTML = `
                            <div class="btn-group">
                                <a href="${localPath}" class="btn btn-primary" download>
                                    <i class="bi bi-download"></i> Download DOCX
                                </a>
                                <a href="https://view.officeapps.live.com/op/view.aspx?src=${encodedUrl}" 
                                   class="btn btn-outline-primary" 
                                   target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i> Open in Office Online
                                </a>
                            </div>
                        `;
                    } else if (fileType === 'txt') {
                        // Handle text files with fetch
                        fetch(localPath)
                            .then(response => {
                                if (!response.ok) throw new Error('Failed to load file');
                                return response.text();
                            })
                            .then(content => {
                                modalBody.innerHTML = `
                                    <pre class="p-4 bg-light rounded" style="max-height: 75vh; overflow-y: auto;">
                                        ${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                                    </pre>
                                `;
                                
                                downloadOptions.innerHTML = `
                                    <div class="btn-group">
                                        <a href="${localPath}" class="btn btn-primary" download>
                                            <i class="bi bi-download"></i> Download Text File
                                        </a>
                                        <a href="${localPath}" class="btn btn-outline-primary" target="_blank">
                                            <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                        </a>
                                    </div>
                                `;
                            })
                            .catch(error => {
                                modalBody.innerHTML = `
                                    <div class="alert alert-danger m-3">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        Error loading file. Please try downloading it instead.
                                    </div>
                                `;
                                
                                downloadOptions.innerHTML = `
                                    <a href="${localPath}" class="btn btn-primary" download>
                                        <i class="bi bi-download"></i> Download File
                                    </a>
                                `;
                            });
                    }
                });
            }
            
            // === NOTIFICATION FUNCTIONALITY ===
            // Check for new notifications
            function checkNewNotifications() {
                const studentID = <?= $studentID ?? 0 ?>;
                const userType = 'student';
                
                if (studentID > 0) {
                    fetch('api/notifications_count.php?user_id=' + studentID + '&user_type=' + userType + '&last_check=' + new Date().toISOString())
                        .then(response => response.json())
                        .then(data => {
                            if (data.count > 0) {
                                // Update notification count in header if badge exists
                                const $badge = document.getElementById('notification-badge');
                                if ($badge) {
                                    $badge.textContent = data.count;
                                    $badge.classList.remove('d-none');
                                }
                                
                                // Play notification sound
                                const notificationSound = document.getElementById('notification-sound');
                                if (notificationSound) {
                                    notificationSound.currentTime = 0;
                                    notificationSound.play().catch(error => console.error('Error playing notification sound:', error));
                                }
                                
                                // Show toast notification for latest notification
                                if (data.latest_notification) {
                                    showToastNotification(data.latest_notification);
                                }
                            }
                        })
                        .catch(error => console.error('Error checking notifications:', error));
                }
            }
            
            // Show toast notification
            function showToastNotification(notification) {
                const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                
                // Play notification sound
                const notificationSound = document.getElementById('notification-sound');
                if (notificationSound) {
                    notificationSound.currentTime = 0;
                    notificationSound.play().catch(error => console.error('Error playing notification sound:', error));
                }
                
                // Remove any existing toast
                const existingToasts = document.querySelectorAll('.toast');
                existingToasts.forEach(toast => toast.remove());
                
                // Create toast container
                const toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                
                // Create toast element with slide-in animation
                const toastEl = document.createElement('div');
                toastEl.className = `toast show ${isDarkMode ? 'bg-dark text-white' : ''}`;
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.style.minWidth = '300px';
                toastEl.style.maxWidth = '90vw';
                toastEl.style.border = 'none';
                toastEl.style.borderRadius = '0.5rem';
                toastEl.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                toastEl.style.animation = 'slideIn 0.5s ease-out forwards';
                
                // Add CSS animation
                const styleEl = document.createElement('style');
                styleEl.textContent = `
                    @keyframes slideIn {
                        from { transform: translateY(100%); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(styleEl);
                
                // Create toast content
                const icon = notification.icon || 'bi-bell-fill';
                toastEl.innerHTML = `
                    <div class="toast-header ${isDarkMode ? 'bg-dark text-white border-secondary' : ''}">
                        <i class="bi ${icon} me-2"></i>
                        <strong class="me-auto">New Notification</strong>
                        <small>Just now</small>
                        <button type="button" class="btn-close ${isDarkMode ? 'btn-close-white' : ''}" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        <h6 class="mb-1">${notification.title}</h6>
                        <p class="mb-0 ${isDarkMode ? 'text-light' : ''}">${notification.message}</p>
                        ${notification.action_url ? `
                            <div class="mt-2 pt-2 border-top ${isDarkMode ? 'border-secondary' : ''}">
                                <a href="${notification.action_url}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </div>
                        ` : ''}
                    </div>
                `;
                
                // Add toast to container
                toastContainer.appendChild(toastEl);
                
                // Add container to body
                document.body.appendChild(toastContainer);
                
                // Add click handler for close button
                const closeBtn = toastEl.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        toastContainer.remove();
                    });
                }
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    toastEl.style.opacity = '0';
                    toastEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    toastEl.style.transform = 'translateY(100%)';
                    
                    setTimeout(() => {
                        if (toastContainer.parentNode) {
                            toastContainer.remove();
                        }
                    }, 500);
                }, 5000);
            }
            
            // Check for notifications when page loads
            setTimeout(checkNewNotifications, 1000);
            
            // Check for new notifications every 30 seconds
            setInterval(checkNewNotifications, 30000);
        });

        // Countdown Timer functionality
        function updateCountdown() {
            <?php if ($currentElection): ?>
            // Election start and end dates from PHP
            const electionStartDate = new Date('<?= isset($currentElection["start_time"]) && $currentElection["start_time"] ? date('Y-m-d', strtotime($currentElection["startDate"])) . 'T' . date('H:i:s', strtotime($currentElection["start_time"])) : date('Y-m-d\TH:i:s', strtotime($currentElection["startDate"])) ?>');
            const electionEndDate = new Date('<?= isset($currentElection["end_time"]) && $currentElection["end_time"] ? date('Y-m-d', strtotime($currentElection["endDate"])) . 'T' . date('H:i:s', strtotime($currentElection["end_time"])) : date('Y-m-d\TH:i:s', strtotime($currentElection["endDate"])) ?>');
            const electionStartDateUTC = new Date(electionStartDate.getTime() + (electionStartDate.getTimezoneOffset() * 60000));
            const electionEndDateUTC = new Date(electionEndDate.getTime() + (electionEndDate.getTimezoneOffset() * 60000));
            
            const currentStatus = '<?= $currentElection["status"] ?>';

            // Get current time in UTC
            const now = new Date(Date.UTC(
                new Date().getUTCFullYear(),
                new Date().getUTCMonth(),
                new Date().getUTCDate(),
                new Date().getUTCHours(),
                new Date().getUTCMinutes(),
                new Date().getUTCSeconds()
            ));


            let targetDate;
            let countdownLabel;

            if (currentStatus === 'Scheduled') {
                targetDate = electionStartDate;
                countdownLabel = 'Election Starts In:';
            } else if (currentStatus === 'Ongoing') {
                targetDate = electionEndDate;
                countdownLabel = 'Election Ends In:';
            } else {
                // Election is not scheduled or ongoing, so hide the timer
                const countdownContainer = document.getElementById('election-countdown');
                if (countdownContainer) {
                    countdownContainer.innerHTML = '<div class="text-center text-warning fw-bold">Election has ended</div>';
                    clearInterval(countdownInterval);
                }
                return;
            }

            // Calculate time remaining in milliseconds
            const timeLeft = targetDate.getTime() - now.getTime();

            if (timeLeft > 0) {
                // Election is still active (scheduled or ongoing)
                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                // Update DOM elements safely
                const daysEl = document.getElementById('days');
                const hoursEl = document.getElementById('hours');
                const minutesEl = document.getElementById('minutes');
                const secondsEl = document.getElementById('seconds');
                const timeRemainingText = document.querySelector('.time-remaining-text');

                if (timeRemainingText) {
                    timeRemainingText.textContent = countdownLabel;
                }

                if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');

            } else {
                // If target date has passed
                const countdownContainer = document.getElementById('election-countdown');
                if (countdownContainer) {
                    if (currentStatus === 'Scheduled') {
                        // If scheduled election start time has passed, it should now be ongoing
                        countdownContainer.innerHTML = '<div class="text-center text-success fw-bold">Election is starting now! Refreshing...</div>';
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000); // Reload after 3 seconds
                    } else if (currentStatus === 'Ongoing') {
                        // For ongoing elections, we should never reach here unless the end date has passed
                        // Double check server time vs client time
                        const serverNow = new Date('<?= date("Y-m-d\TH:i:s") ?>');
                        const endDate = new Date('<?= date("Y-m-d\TH:i:s", strtotime($currentElection["endDate"])) ?>');

                        if (serverNow >= endDate) {
                            // If server time confirms election has ended
                            countdownContainer.innerHTML = '<div class="text-center text-warning fw-bold">Election has ended</div>';

                            // Disable voting form and redirect to results
                            const votingForm = document.getElementById('votingForm');
                            if (votingForm) {
                                votingForm.style.display = 'none';
                                const endedMessage = document.createElement('div');
                                endedMessage.className = 'alert alert-warning text-center';
                                endedMessage.innerHTML = '<i class="bi bi-clock-history me-2"></i>This election has concluded. Results should be available soon.';
                                votingForm.parentNode.insertBefore(endedMessage, votingForm);

                                const resultsButton = document.createElement('a');
                                resultsButton.href = 'live_results.php?election=<?= $currentElection["electionID"] ?>';
                                resultsButton.className = 'btn btn-primary d-block mt-3';
                                resultsButton.innerHTML = '<i class="bi bi-bar-chart-fill me-2"></i>View Election Results';
                                endedMessage.appendChild(resultsButton);

                                setTimeout(() => {
                                    window.location.href = 'live_results.php?election=<?= $currentElection["electionID"] ?>';
                                }, 5000);
                            }

                            // Clear the interval to stop the countdown
                            clearInterval(countdownInterval);
                        } else {
                            // If client time is ahead of server time, recalculate with server time
                            countdownContainer.innerHTML = '<div class="d-flex align-items-center justify-content-start countdown-container">' +
                                '<div class="time-unit"><span>00</span><small>days</small></div>' +
                                '<div class="time-separator">:</div>' +
                                '<div class="time-unit"><span>00</span><small>hours</small></div>' +
                                '<div class="time-separator">:</div>' +
                                '<div class="time-unit"><span>00</span><small>minutes</small></div>' +
                                '<div class="time-separator">:</div>' +
                                '<div class="time-unit"><span>00</span><small>seconds</small></div>' +
                            '</div>';

                            // Force refresh to get updated election status
                            setTimeout(() => {
                                window.location.reload();
                            }, 5000);
                        }
                    } else {
                        // For completed elections
                        countdownContainer.innerHTML = '<div class="text-center text-warning fw-bold">Election has ended</div>';
                        clearInterval(countdownInterval);
                    }
                }
            }
            <?php endif; ?>
        }

        let countdownInterval; // Define interval variable in a scope accessible by clearInterval
        <?php if ($currentElection): ?>
            countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown(); // Initial call to display immediately
        <?php endif; ?>
        
        // Create bubble backgrounds for election timer and info sections
        function createBubbles() {
            // Get all bubble background elements
            const bubbleContainers = document.querySelectorAll('.bubble-background');
            
            bubbleContainers.forEach(container => {
                // Remove any existing bubbles first (for theme changes)
                container.querySelectorAll('.bubble').forEach(bubble => bubble.remove());
                
                // Create between 10-20 bubbles based on container size
                const containerWidth = container.offsetWidth;
                const containerHeight = container.offsetHeight;
                const numberOfBubbles = Math.max(10, Math.floor(containerWidth * containerHeight / 8000));
                const maxBubbles = Math.min(20, numberOfBubbles);
                
                // Get colors from CSS variables
                const computedStyle = getComputedStyle(container);
                const bubbleColorRGB = computedStyle.getPropertyValue('--bubble-color-rgb').trim();
                
                // Create bubble layers for 3D effect
                for (let layer = 1; layer <= 3; layer++) {
                    const layerBubbleCount = Math.ceil(maxBubbles / 3);
                    const zIndex = layer * 10 - 10; 
                    const opacity = 0.05 + (layer * 0.05); // Opacity increases with each layer
                    
                    for (let i = 0; i < layerBubbleCount; i++) {
                        const bubble = document.createElement('div');
                        bubble.classList.add('bubble');
                        
                        // Size varies by layer - deeper layers have smaller bubbles
                        const baseSize = 10 + (layer * 15); // Layer 1: 25px base, Layer 2: 40px base, Layer 3: 55px base
                        const sizeVariation = 10 + (layer * 5); // Variation increases with layer
                        const size = Math.floor(Math.random() * sizeVariation) + baseSize;
                        
                        bubble.style.width = `${size}px`;
                        bubble.style.height = `${size}px`;
                        
                        // Random position
                        const left = Math.floor(Math.random() * (containerWidth - size));
                        const top = Math.floor(Math.random() * (containerHeight - size));
                        bubble.style.left = `${left}px`;
                        bubble.style.top = `${top}px`;
                        
                        // Layer-specific styles
                        bubble.style.zIndex = zIndex;
                        bubble.style.setProperty('--bubble-opacity', opacity);
                        bubble.style.setProperty('--bubble-blur', `${4 - layer}px`); // Deeper layers are blurrier
                        
                        // More organic shape with border-radius variations
                        if (Math.random() > 0.7) {
                            // Create slightly oval bubble
                            const randomBorderRadius = `${Math.floor(40 + Math.random() * 20)}% ${Math.floor(40 + Math.random() * 20)}% ${Math.floor(40 + Math.random() * 20)}% ${Math.floor(40 + Math.random() * 20)}%`;
                            bubble.style.borderRadius = randomBorderRadius;
                        }
                        
                        // Random float animation properties - deeper layers move more slowly
                        const floatTime = Math.floor((Math.random() * 8) + 10 - (layer * 2)); // 4-12s
                        const glowTime = Math.floor((Math.random() * 10) + 5); // 5-15s
                        const pulseTime = Math.floor((Math.random() * 5) + 2); // 2-7s
                        
                        // Movement range decreases with layer depth
                        const movementFactor = 1 - ((layer - 1) * 0.2); // Layer 1: 0.8, Layer 2: 0.6, Layer 3: 0.4
                        const floatY = Math.floor(Math.random() * 50 * movementFactor) - (25 * movementFactor); 
                        const floatX = Math.floor(Math.random() * 50 * movementFactor) - (25 * movementFactor);
                        const rotate = Math.floor(Math.random() * 30) - 15; // -15 to 15 degrees rotation
                        const floatScale = (Math.random() * 0.3 * movementFactor) + 0.85; // Scale variation 0.85-1.15
                        
                        bubble.style.setProperty('--float-time', `${floatTime}s`);
                        bubble.style.setProperty('--glow-time', `${glowTime}s`);
                        bubble.style.setProperty('--pulse-time', `${pulseTime}s`);
                        bubble.style.setProperty('--float-y', `${floatY}px`);
                        bubble.style.setProperty('--float-x', `${floatX}px`);
                        bubble.style.setProperty('--rotate', `${rotate}deg`);
                        bubble.style.setProperty('--float-scale', floatScale);
                        
                        // Make some bubbles pulse
                        if (Math.random() > 0.5) {
                            bubble.classList.add('pulse');
                        }
                        
                        // Add custom gradient to some bubbles for more realism
                        if (Math.random() > 0.3) {
                            const gradientAngle = Math.floor(Math.random() * 360);
                            const gradientStart = `rgba(${bubbleColorRGB}, ${opacity * 3})`;
                            const gradientEnd = `rgba(${bubbleColorRGB}, ${opacity / 2})`;
                            bubble.style.background = `radial-gradient(circle at ${Math.floor(Math.random() * 70) + 15}% ${Math.floor(Math.random() * 70) + 15}%, ${gradientStart} 0%, ${gradientEnd} 80%)`;
                        }
                        
                        // Append bubble to container
                        container.appendChild(bubble);
                    }
                }
            });
        }
        
        // Create bubbles on page load with a small delay to ensure container sizes are calculated correctly
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(createBubbles, 100);
            
            // Recreate bubbles when theme changes to update colors
            document.addEventListener('themeChanged', function() {
                setTimeout(createBubbles, 100); // Small delay for theme transition
            });
            
            // Recreate bubbles on window resize
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(createBubbles, 300);
            });
        });
    </script>
</body>
</html>