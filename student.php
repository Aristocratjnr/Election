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

        // Debug positions after deduplication
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
            
            // For debugging
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
                $firstVote = $votes[0]; 
                
                $simpleSQL = "INSERT INTO votes (electionID, candidateID, studentID, timestamp) VALUES (?, ?, ?, NOW())";
                $simpleStmt = $conn->prepare($simpleSQL);
                $simpleStmt->bind_param('iii', 
                    $firstVote['electionID'], 
                    $firstVote['candidateID'], 
                    $firstVote['studentID']
                );
                $insertResult = $simpleStmt->execute();
                $insertCount = $simpleStmt->affected_rows;
                $simpleStmt->close();
                
                if (!$insertResult || $insertCount === 0) {
                    throw new Exception("Failed to record your vote. Database error: " . $conn->error);
                }
            } else {
                throw new Exception("No votes to record. Please select at least one candidate.");
            }
            
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

            // Commit transaction
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Light mode variables (default) */
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
            --header-bg: #ffffff;
            --shadow-color: rgba(0,0,0,0.05);
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            /* Default Bootstrap theme for light mode */
            color-scheme: light;
        }
        
        /* Dark mode variables */
        [data-bs-theme="dark"] {
            --primary: #6ea8fe;
            --primary-light: rgba(110, 168, 254, 0.15);
            --primary-dark: #3a56d4;
            --success: #75b798;
            --success-light: rgba(117, 183, 152, 0.15);
            --surface: #2b3035;
            --surface-hover: #343a40;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            --card-hover-shadow: 0 15px 35px rgba(110, 168, 254, 0.25);
            --text: #f8f9fa;
            --text-muted: #adb5bd;
            --border: #495057;
            --bg: #212529;
            --header-bg: #343a40;
            --shadow-color: rgba(0,0,0,0.2);
            --danger: #ea868f;
            --warning: #ffda6a;
            --info: #6edff6;
            
            /* Default Bootstrap theme for dark mode */
            color-scheme: dark;
        }
        
        body {
            background-color: var(--bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            line-height: 1.5;
        }
        
        /* Header dark mode styles */
        #header {
            background-color: var(--header-bg);
            transition: background-color 0.3s ease;
        }

        [data-bs-theme="dark"] #header {
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border);
        }

        [data-bs-theme="dark"] #header .nav-link,
        [data-bs-theme="dark"] #header .dropdown-toggle {
            color: var(--text);
        }

        /* Logo dark mode visibility */
        [data-bs-theme="dark"] .logo span {
            color: var(--text);
        }

        /* Live results text in dark mode */
        [data-bs-theme="dark"] .nav-link span {
            color: var(--text) !important;
        }

        /* Button icons in dark mode */
        [data-bs-theme="dark"] .btn-link i {
            color: var(--text);
        }

        /* Mobile toggle button */
        [data-bs-theme="dark"] .toggle-sidebar-btn {
            color: var(--text);
        }

        /* Search toggle */
        [data-bs-theme="dark"] .search-toggle {
            color: var(--text);
        }

        /* Dropdown menu in dark mode */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: var(--surface);
            border-color: var(--border);
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: var(--text);
        }

        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: var(--surface-hover);
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
            box-shadow: 0 2px 5px var(--shadow-color);
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
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
        
        /* Position section improvements for dark mode */
        .position-section {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            transition: border-color 0.3s ease;
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
            box-shadow: 0 4px 12px var(--shadow-color);
            margin-top: 1rem;
            width: 100%;
            text-align: center;
            color: white;
        }
        
        
        
        .election-timer {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow-color);
        }
        
        .election-timer::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
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
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            padding: 18px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 10px var(--shadow-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .student-avatar {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid var(--surface);
            box-shadow: 0 4px 10px var(--shadow-color);
            transition: border-color 0.3s ease;
        }
        
        .student-details h5 {
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px var(--shadow-color);
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
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .candidate-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }

        .candidate-main {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
        }

        .candidate-details {
            flex: 1;
            min-width: 0;
        }

        .candidate-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.3rem;
            color: var(--text);
            transition: all 0.3s ease;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            line-height: 1.2;
        }

        .candidate-position {
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            background: rgba(67, 97, 238, 0.1);
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 0.3rem;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .vote-stats {
            background: var(--surface-hover);
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            box-shadow: inset 0 1px 3px var(--shadow-color);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .vote-stats i {
            font-size: 0.9rem;
        }

        .vote-count, .vote-percentage {
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .candidate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-right: 0.8rem;
            flex-shrink: 0;
        }

        .candidate-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: var(--surface-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
            border: 3px solid var(--surface);
            box-shadow: 0 4px 10px var(--shadow-color);
            margin-right: 0.8rem;
            flex-shrink: 0;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background-color: var(--surface-hover);
            overflow: hidden;
            box-shadow: inset 0 1px 3px var(--shadow-color);
            margin-top: 0.5rem;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: progressShine 2s infinite;
        }

        .rank-badge {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--primary-light);
            box-shadow: 0 2px 8px var(--shadow-color);
            position: relative;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .rank-badge i {
            font-size: 1rem;
        }

        @keyframes pulsate {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
            
            .candidate-avatar, .candidate-avatar-placeholder {
                width: 50px;
                height: 50px;
            }
            
            .candidate-name {
                font-size: 0.9rem;
            }
            
            .vote-stats {
                padding: 0.3rem 0.6rem;
            }
            
            .candidate-result-card {
                min-height: 150px;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            clip-path: polygon(0% 0%, 100% 0%, 50% 50%);
        }

        .welcome-body {
            padding: 2rem 1.5rem;
        }

        .tip-card {
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            transition: all 0.3s ease;
            background-color: white;
            display: flex;
            align-items: flex-start;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .tip-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .tip-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .tip-content {
            flex: 1;
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
            transition: transform 0.5s ease;
        }

        .welcome-illustration:hover {
            transform: scale(1.05);
        }

        .btn-get-started {
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .btn-get-started:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
            background: linear-gradient(135deg, #3a56d4 0%, #2e44c2 100%);
            color: white;
        }

        @media (max-width: 767.98px) {
            .welcome-body {
                padding: 1.5rem 1rem;
            }
            
            .tip-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .tip-icon {
                width: 36px;
                height: 36px;
                font-size: 1rem;
                margin-right: 0.75rem;
            }
            
            .welcome-header::after {
                bottom: -10px;
                width: 20px;
                height: 20px;
            }
            
            .tip-card h5 {
                font-size: 1rem;
                margin-bottom: 0.25rem;
            }
            
            .tip-card p {
                font-size: 0.875rem;
                margin-bottom: 0;
            }
        }

        @media (max-width: 575.98px) {
            .welcome-modal {
                border-radius: 12px;
            }
            
            .welcome-header {
                padding: 1.25rem 1rem;
            }
            
            .welcome-body {
                padding: 1.25rem 0.75rem;
            }
            
            .btn-get-started {
                width: 100%;
            }
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
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
            margin-top: 1rem;
        }
        
        .live-results-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 10px;
        }
        
        .live-indicator {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
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
        
        .status-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.15), rgba(67, 97, 238, 0.05));
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
        }
        
        .status-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f5;
        }
        
        .status-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.08);
        }

        /* Modal and UI Component Dark Mode Support */
        .modal-content {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .modal-header, .modal-footer {
            border-color: var(--border);
            transition: border-color 0.3s ease;
        }

        /* Form controls */
        .form-control {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .form-control:focus {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem var(--primary-light);
        }

        /* Buttons */
        .btn-outline-secondary {
            color: var(--text-muted);
            border-color: var(--border);
        }

        .btn-outline-secondary:hover {
            background-color: var(--surface-hover);
            color: var(--text);
            border-color: var(--text-muted);
        }

        /* Make sure selection labels are visible in dark mode */
        .form-check-label {
            color: var(--text);
            transition: color 0.3s ease;
        }

        /* Ensure badges have proper dark mode colors */
        .badge {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Update manifesto button styling for dark mode */
        .manifesto-btn {
            color: var(--primary);
            background-color: var(--primary-light);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .manifesto-btn:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Position container and headings */
        .position-container {
            border-bottom: 1px solid var(--border);
            transition: border-color 0.3s ease;
        }

        .position-title {
            color: var(--text);
            transition: color 0.3s ease;
        }

        .candidates-row {
            transition: background-color 0.3s ease;
        }

        /* Ensure tooltips are visible in dark mode */
        .tooltip .tooltip-inner {
            background-color: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: 0 2px 10px var(--shadow-color);
        }

        .bs-tooltip-auto[x-placement^=top] .arrow::before, 
        .bs-tooltip-top .arrow::before {
            border-top-color: var(--border);
        }

        /* Alerts for error messages */
        .alert-danger {
            background-color: rgba(var(--danger-rgb, 220, 53, 69), 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert-warning {
            background-color: rgba(var(--warning-rgb, 255, 193, 7), 0.1);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        /* Card and section backgrounds */
        .card {
            background-color: var(--surface);
            border-color: var(--border);
            color: var(--text);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .card-header, .card-footer {
            background-color: var(--surface-hover);
            border-color: var(--border);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        /* Update modal header background colors */
        .modal-header.bg-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
        }

        .modal-header.bg-success {
            background: linear-gradient(135deg, var(--success), var(--success-dark, #198754)) !important;
        }

        .modal-header.bg-warning {
            background: linear-gradient(135deg, var(--warning), var(--warning-dark, #f59e0b)) !important;
        }

        .modal-header.bg-danger {
            background: linear-gradient(135deg, var(--danger), var(--danger-dark, #dc3545)) !important;
        }

        /* Gradient buttons */
        .gradient-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .gradient-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        /* Carousel adjustments for dark mode */
        .carousel-item {
            background-color: var(--surface);
            color: var(--text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .carousel-control-prev-icon, 
        .carousel-control-next-icon {
            filter: none;
            background-color: var(--primary-light);
            border-radius: 50%;
            padding: 10px;
        }

        /* Ensure carousel text is visible in dark mode */
        .carousel-item .text-muted {
            color: var(--text-muted) !important;
        }

        .carousel-item h5 {
            color: var(--text);
            transition: color 0.3s ease;
        }

        /* Fix welcome illustration */
        .welcome-illustration {
            border: 1px solid var(--border);
            background-color: var(--surface-hover);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        /* Add styles for the vote portal and voting form container */
        .voting-portal, .voting-form-container {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        /* Make sure position headings are visible in dark mode */
        .position-title {
            color: var(--text);
            transition: color 0.3s ease;
        }

        /* Fix main container background to adapt to dark mode */
        .container {
            background-color: var(--bg);
            color: var(--text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Fix header background color for specific sections */
        .section-header {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        /* Update manifesto link buttons */
        .manifesto-link {
            color: var(--primary);
            transition: color 0.3s ease;
        }

        .manifesto-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Update checkboxes for dark mode */
        .form-check-input {
            background-color: var(--surface-hover);
            border-color: var(--border);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Dashboard cards and statistics for dark mode */
        .dashboard-card {
            background-color: var(--surface);
            border-color: var(--border);
            color: var(--text);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .dashboard-card .card-title {
            color: var(--text);
        }

        .dashboard-card .card-text {
            color: var(--text-secondary);
        }

        /* Statistics counter styles */
        .stats-counter {
            color: var(--primary);
        }

        .stats-label {
            color: var(--text-secondary);
        }

        /* Alert messages for dark mode */
        .alert {
            border-color: var(--border);
        }

        .alert-info {
            background-color: rgba(var(--info-rgb), 0.2);
            color: var(--info);
        }

        .alert-success {
            background-color: rgba(var(--success-rgb), 0.2);
            color: var(--success);
        }

        .alert-warning {
            background-color: rgba(var(--warning-rgb), 0.2);
            color: var(--warning);
        }

        .alert-danger {
            background-color: rgba(var(--danger-rgb), 0.2);
            color: var(--danger);
        }

        /* Table styles for results and voting interfaces */
        table.candidate-table {
            border-color: var(--border);
            background-color: var(--surface);
            color: var(--text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        table.candidate-table th {
            background-color: var(--surface-variant);
            color: var(--text);
            border-color: var(--border);
        }

        table.candidate-table td {
            border-color: var(--border);
        }

        /* Candidate card styles */
        .candidate-card {
            background-color: var(--surface);
            border-color: var(--border);
            color: var(--text);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .candidate-card:hover {
            background-color: var(--surface-variant);
        }

        .candidate-name {
            color: var(--primary);
            font-weight: bold;
        }

        /* Pagination styles */
        .pagination .page-link {
            background-color: var(--surface);
            border-color: var(--border);
            color: var(--text);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--on-primary);
        }

        .pagination .page-link:hover {
            background-color: var(--surface-variant);
            color: var(--primary);
        }

        .live-results-text {
            color: var(--text);
        }

        [data-bs-theme="light"] .live-results-text {
            color: #2b3445;
        }

        .card-header.bg-white {
            background-color: var(--surface) !important;
            color: var(--text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Fix voting portal card header in dark mode */
        [data-bs-theme="dark"] .card-header.bg-white,
        [data-bs-theme="dark"] .bg-white {
            background-color: var(--surface) !important;
            color: var(--text);
        }

        /* Ensure the voting portal section changes color in dark mode */
        [data-bs-theme="dark"] #header ~ main .voting-card .card-header {
            background-color: var(--surface) !important;
            color: var(--text);
        }

        /* Fix text colors in dark mode for voting portal */
        [data-bs-theme="dark"] h2,
        [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] h4,
        [data-bs-theme="dark"] h5,
        [data-bs-theme="dark"] p:not(.text-muted) {
            color: var(--text);
        }

        /* Fix any hardcoded white backgrounds */
        [data-bs-theme="dark"] .bg-white,
        [data-bs-theme="dark"] [class*="bg-light"],
        [data-bs-theme="dark"] .bg-gradient-light {
            background-color: var(--surface) !important;
        }

        /* Dark mode fixes for student.php */
        [data-bs-theme="dark"] .voting-card {
            background-color: var(--surface);
        }

        [data-bs-theme="dark"] .student-info {
            background-color: var(--surface);
            border-color: var(--border);
        }

        [data-bs-theme="dark"] .candidate-card {
            background-color: var(--surface);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        [data-bs-theme="dark"] .candidate-card.selected {
            background-color: var(--primary-light);
            border-color: var(--primary);
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: var(--surface) !important;
        }

        [data-bs-theme="dark"] .sticky-bottom {
            background-color: var(--surface);
            border-color: var(--border);
        }

        /* Ensure proper contrast for candidate details */
        [data-bs-theme="dark"] .candidate-name {
            color: var(--text);
        }

        [data-bs-theme="dark"] .badge.bg-primary.bg-opacity-10 {
            background-color: rgba(110, 168, 254, 0.2) !important;
            color: var(--primary);
        }

        /* Fix the department badge color in dark mode */
        [data-bs-theme="dark"] .department-badge {
            background-color: var(--surface);
            color: var(--primary);
            border-color: var(--primary-light);
        }

        /* Fix election timer in dark mode */
        [data-bs-theme="dark"] .election-timer {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Fix alert backgrounds in dark mode */
        [data-bs-theme="dark"] .alert-light {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
        }

        /* Fix any bg-light sections within candidate cards */
        [data-bs-theme="dark"] .candidate-card .bg-light {
            background-color: rgba(0, 0, 0, 0.2) !important;
        }

        /* Fix the candidate tagline background */
        [data-bs-theme="dark"] .candidate-tagline.bg-light {
            background-color: rgba(0, 0, 0, 0.2) !important;
            color: var(--text);
        }

        /* Fix card and section backgrounds */
        [data-bs-theme="dark"] .card {
            background-color: var(--surface);
            border-color: var(--border);
        }

        /* Fix voting status badge colors */
        [data-bs-theme="dark"] .voting-status.voting-active {
            background-color: rgba(110, 168, 254, 0.2);
        }

        [data-bs-theme="dark"] .voting-status.voting-inactive {
            background-color: rgba(173, 181, 189, 0.2);
        }

        /* Dark mode improvements */
        [data-bs-theme="dark"] .voting-card,
        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .card-body,
        [data-bs-theme="dark"] .card-footer,
        [data-bs-theme="dark"] .sticky-bottom,
        [data-bs-theme="dark"] .bg-white {
            background-color: var(--surface) !important;
            color: var(--text) !important;
            border-color: var(--border) !important;
        }

        [data-bs-theme="dark"] .student-info {
            background-color: var(--surface) !important;
            border-color: var(--border) !important;
        }

        [data-bs-theme="dark"] .candidate-card {
            background-color: var(--surface) !important;
            border-color: var(--border) !important;
        }

        [data-bs-theme="dark"] .candidate-card .bg-light,
        [data-bs-theme="dark"] .alert-light,
        [data-bs-theme="dark"] .candidate-tagline.bg-light {
            background-color: rgba(0, 0, 0, 0.2) !important;
            color: var(--text) !important;
        }

        [data-bs-theme="dark"] h2, 
        [data-bs-theme="dark"] h3, 
        [data-bs-theme="dark"] h4, 
        [data-bs-theme="dark"] h5, 
        [data-bs-theme="dark"] h6 {
            color: var(--text) !important;
        }

        /* Specific fix for voting portal text */
        [data-bs-theme="dark"] .voting-card .card-header h2 {
            color: var(--text) !important;
        }

        [data-bs-theme="dark"] .voting-status {
            color: var(--text) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: var(--surface) !important;
            border-color: var(--border);
        }

        [data-bs-theme="dark"] .voting-status.voting-active {
            background-color: rgba(var(--primary-rgb, 67, 97, 238), 0.2) !important;
            color: var(--primary) !important;
        }

        [data-bs-theme="dark"] .election-timer {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Override any inline styles that might be causing issues */
        [data-bs-theme="dark"] [style*="background-color: white"],
        [data-bs-theme="dark"] [style*="background-color: #fff"],
        [data-bs-theme="dark"] [style*="background-color:#fff"],
        [data-bs-theme="dark"] [style*="background-color:#ffffff"],
        [data-bs-theme="dark"] [style*="background-color: #ffffff"],
        [data-bs-theme="dark"] [style*="background:white"],
        [data-bs-theme="dark"] [style*="background: white"],
        [data-bs-theme="dark"] [style*="background:#fff"],
        [data-bs-theme="dark"] [style*="background: #fff"] {
            background-color: var(--surface) !important;
        }

        /* High specificity overrides for dark mode */
        html[data-bs-theme="dark"] .bg-light,
        html[data-bs-theme="dark"] .bg-white,
        html[data-bs-theme="dark"] [class*="bg-light"],
        html[data-bs-theme="dark"] [class*="bg-white"] {
            background-color: var(--surface) !important;
            color: var(--text) !important;
        }

        html[data-bs-theme="dark"] .candidate-card,
        html[data-bs-theme="dark"] .candidate-card div,
        html[data-bs-theme="dark"] .voting-card,
        html[data-bs-theme="dark"] .voting-card .card-header {
            background-color: var(--surface) !important;
            color: var(--text) !important;
        }

        html[data-bs-theme="dark"] .candidate-tagline {
            background-color: rgba(0, 0, 0, 0.2) !important;
        }

        html[data-bs-theme="dark"] .sticky-bottom {
            background-color: var(--surface) !important;
            border-color: var(--border) !important;
        }
    </style>
</head>
<body>
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
                        <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-gradient-primary text-white py-3 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0 fw-bold">Election Results</h5>
                                        <p class="mb-0 opacity-75 small">Live updates from the voting system</p>
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
                                                                <i class="bi bi-building me-1"></i>
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
         document.addEventListener('DOMContentLoaded', function() {
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
                    const manifestoContent = button.getAttribute('data-manifesto');
                    const modalBody = manifestoModal.querySelector('.manifesto-content');
                    modalBody.textContent = manifestoContent;
                });
            }
        });

       </script>
</body>
</html>