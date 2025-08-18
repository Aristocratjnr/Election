<?php
session_start();
require 'configs/dbconnection.php';
require 'configs/session.php';
require_once('classes/Blockchain.php'); // Add Blockchain class

// Initialize blockchain
$blockchain = new Blockchain($conn);

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
    // Fetch only current ongoing election
    $stmt = $conn->prepare("
        SELECT * FROM elections 
        WHERE status = 'Ongoing'
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
                        
                        // Get the ID of the inserted vote for blockchain
                        $voteID = $conn->insert_id;
                        
                        // Add the vote to the blockchain
                        if (!$blockchain->addVote(
                            $vote['electionID'],
                            $vote['studentID'],
                            $vote['candidateID'],
                            $voteID
                        )) {
                            throw new Exception("Failed to secure vote in blockchain for candidate ID: " . $vote['candidateID']);
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

            // Store last vote ID in session for verification purposes
            $_SESSION['last_vote_id'] = $voteID;
            $_SESSION['vote_success'] = true;

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link rel="manifest" href="/Election/manifest.json">
    <meta name="theme-color" content="#4e73df">
    <link href="assets/css/student-portal.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
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
                                <h2 class="mb-1 fw-bold"><i class="bi bi-card-checklist role-icon icon"></i>&nbsp;Voting Portal</h2>
                                <p class="text-muted mb-0">Cast your vote for the student leadership election  <i class="bi bi-clipboard-check department-icon icon"></i></p>
                            </div>
                            <div class="voting-status <?= $currentElection ? 'voting-active pulse-badge' : 'voting-inactive' ?>">
                                <i class="bi <?= $currentElection ? 'bi-broadcast' : 'bi-x-circle' ?> me-2"></i>
                                <?= $currentElection ? 'Election in Progress' : 'No Active Election' ?>
                            </div>
                        </div>
                    </div>
                      <div class="card-body p-4 ">
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
                                <h5 > <i class="bi bi-person-vcard profile-icon icon"></i>&nbsp;<?= htmlspecialchars($student['name'] ?? 'Student') ?></h5>
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

                        <?php if ($currentElection && $currentElection['status'] === 'Ongoing'): ?>
                            <div class="election-timer mb-4">
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
                                        </div>                                        <p class="election-date mt-2 mb-0  alight-item-center justify-content-center"><i class="bi bi-calendar-event me-1"></i>Ends on: <?= date('F j, Y', strtotime($currentElection['endDate'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
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
                                <?php if ($currentElection): ?>
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
                                        <div class="d-flex justify-content-between align-items-center mb-3">
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
                                                                            <span class="vote-percentage"><?= $votePercentage ?>% votes</span>
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
                                <?php endif; ?>
                            </div>
                        </div>
                        
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
                                                    <div class="manifesto-btn p-2 rounded text-center" data-bs-toggle="modal" data-bs-target="#manifestoModal" data-manifesto="<?= htmlspecialchars($candidate['manifesto']) ?>">
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
        </div>
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

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Session Timeout Script -->
<script src="assets/js/session-timeout.js"></script>
<script>
    // Theme management
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
                finalSubmitBtn.addEventListener('click', function() {                    const form = document.getElementById('votingForm');
                    if (form) {
                        // Add submit_vote parameter
                        const submitInput = document.createElement('input');
                        submitInput.type = 'hidden';
                        submitInput.name = 'submit_vote';
                        submitInput.value = '1';
                        form.appendChild(submitInput);
                        
                        // Add click visual feedback
                        this.classList.add('btn-clicked');
                        
                        // Optional: add haptic feedback if supported
                        if (window.navigator && window.navigator.vibrate) {
                            window.navigator.vibrate(50);
                        }
                        
                        // Show loading state with slight delay for better visual effect
                        setTimeout(() => {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
                            
                            // Submit the form
                            form.submit();
                        }, 150);
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
                    const manifestoContent = button.getAttribute('data-manifesto');
                    const modalBody = manifestoModal.querySelector('.manifesto-content');
                    modalBody.textContent = manifestoContent;
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
                        .then((data) => {
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

        // Add bubble pop effect to time unit
        function addBubblePop(element, intensity = 'normal') {
            if (!element) return;
            
            // Create bubbles for animation - adjust count based on intensity
            const bubbleCount = intensity === 'high' ? 6 : 
                               intensity === 'low' ? 2 : 4; // Reduced number of bubbles 
            
            for (let i = 0; i < bubbleCount; i++) {
                let bubble = document.createElement('div');
                bubble.className = 'bubble-pop';
                
                // Randomize position within the element - more centered
                bubble.style.left = (20 + Math.random() * 60) + '%'; 
                bubble.style.top = (30 + Math.random() * 40) + '%';  
                
                // Slower animation 
                bubble.style.animationDelay = (Math.random() * 0.2) + 's';
                bubble.style.animationDuration = (1.2 + Math.random() * 0.8) + 's';
                
                // Subtler size
                const size = 3 + Math.random() * 6;
                bubble.style.width = size + 'px';
                bubble.style.height = size + 'px';
                
                // Softer colors with reduced opacity
                const colors = ['rgba(67, 97, 238, 0.5)', 'rgba(255, 255, 255, 0.5)', 'rgba(94, 114, 228, 0.5)'];
                const color = colors[Math.floor(Math.random() * colors.length)];
                bubble.style.backgroundColor = color;
                bubble.style.boxShadow = '0 0 ' + (size/2) + 'px ' + color;
                bubble.style.opacity = '0.6'; // Reduced overall opacity
                
                // Add to element
                element.appendChild(bubble);
                
                // Remove bubble after animation completes
                setTimeout(() => {
                    if (bubble && bubble.parentNode) {
                        bubble.parentNode.removeChild(bubble);
                    }
                }, 2000); // Longer to account for slower animations
            }
              // Add a more subtle highlight effect to the time unit
            element.style.transition = 'all 0.3s ease-in-out';
            const originalBoxShadow = element.style.boxShadow;
            
            // Only add the subtle glow on higher intensity effects
            if (intensity !== 'low') {
                element.style.boxShadow = '0 0 8px rgba(67, 97, 238, 0.3)';
                
                // Reset back after animation with a smoother transition
                setTimeout(() => {
                    element.style.boxShadow = originalBoxShadow;
                }, 400);
            }
        }
        
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
                }                // Store previous values to detect changes
                const prevSeconds = secondsEl ? secondsEl.textContent : '';
                const prevMinutes = minutesEl ? minutesEl.textContent : '';
                const prevHours = hoursEl ? hoursEl.textContent : '';
                const prevDays = daysEl ? daysEl.textContent : '';
                
                // Update the values
                if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
                if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
                  // Add bubble pop effect when values change
                if (secondsEl && prevSeconds !== String(seconds).padStart(2, '0')) {
                    // Only add bubbles on multiples of 10 seconds or at 0
                    if (seconds % 10 === 0 || seconds === 0) {
                        addBubblePop(secondsEl.closest('.time-unit'), 'low');
                    }
                }
                
                if (minutesEl && prevMinutes !== String(minutes).padStart(2, '0')) {
                    // Minutes change is more significant, use normal intensity
                    addBubblePop(minutesEl.closest('.time-unit'), 'normal');
                }
                
                if (hoursEl && prevHours !== String(hours).padStart(2, '0')) {
                    // Hour change is most significant, use high intensity
                    addBubblePop(hoursEl.closest('.time-unit'), 'high');
                }                
                if (daysEl && prevDays !== String(days).padStart(2, '0') && days > 0) {
                    // Day changes are very significant, use high intensity
                    addBubblePop(daysEl.closest('.time-unit'), 'high');
                }

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
                                }, 3000);
                            }
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
    </script>
    <script>
  // Register Service Worker for PWA
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/Election/sw.js')
        .then(function(reg) { console.log('Service Worker registered:', reg.scope); })
        .catch(function(err) { console.error('SW registration failed:', err); });
    });
  }
  // Handle PWA install prompt
  let deferredPrompt;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    // Create install button
    const installButton = document.createElement('div');
    installButton.id = 'installButton';    installButton.className = 'position-fixed bottom-0 end-0 m-3';
    installButton.style.cursor = 'pointer';
    installButton.style.zIndex = '1040';
    installButton.style.transition = 'all 0.3s ease';
    installButton.title = 'Install SmartVote';
    installButton.innerHTML = `
            <button id="installBtn" class="btn btn-sm btn-primary rounded-pill shadow-sm d-flex align-items-center">
                <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Install App</span>
            </button>
        `;
    document.body.appendChild(installButton);
    installButton.addEventListener('click', () => {
      installButton.remove();
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(() => deferredPrompt = null);
    });
  });
</script>
</body>
</html>