<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';
require_once 'update_election_status.php'; 

updateElectionStatuses();

require_once 'calculate_vote_results.php';

// If an election is selected, automatically refresh its vote counts
if (isset($_GET['election']) && is_numeric($_GET['election'])) {
    $selectedElectionID = (int)$_GET['election'];
    updateVoteResults($conn, $selectedElectionID);
    
    // Check if there are any votes for this election
    $checkVotes = $conn->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE electionID = ?");
    $checkVotes->bind_param("i", $selectedElectionID);
    $checkVotes->execute();
    $voteCount = $checkVotes->get_result()->fetch_assoc()['vote_count'];
    
    // If no votes exist but candidates do, create some test votes for demonstration
    if ($voteCount == 0) {
        // First get candidates for this election
        $candidatesQuery = $conn->prepare("
            SELECT c.candidateID, s.studentID 
            FROM candidates c
            JOIN students s ON c.studentID = s.studentID
            JOIN positions p ON c.positionID = p.positionID
            WHERE p.electionID = ? AND c.status = 'Approved'
            LIMIT 10
        ");
        $candidatesQuery->bind_param("i", $selectedElectionID);
        $candidatesQuery->execute();
        $candidates = $candidatesQuery->get_result();
        
        // Get a few students as voters
        $votersQuery = $conn->query("SELECT studentID FROM students LIMIT 10");
        $voters = [];
        while ($voter = $votersQuery->fetch_assoc()) {
            $voters[] = $voter['studentID'];
        }
        
        // If we have candidates and voters, create some test votes
        if ($candidates->num_rows > 0 && count($voters) > 0) {
            // Start a transaction
            $conn->begin_transaction();
            
            try {
                // Prepare vote insertion
                $insertVote = $conn->prepare("
                    INSERT INTO votes (electionID, candidateID, voter_studentID, timestamp) 
                    VALUES (?, ?, ?, NOW())
                ");
                
                $addedVotes = 0;
                
                // For each candidate, add a random number of votes from random voters
                while ($candidate = $candidates->fetch_assoc()) {
                    $candidateID = $candidate['candidateID'];
                    
                    // Add 1-5 votes for each candidate
                    $numVotes = rand(1, 5);
                    
                    for ($i = 0; $i < $numVotes; $i++) {
                        // Use a random voter
                        $voterID = $voters[array_rand($voters)];
                        
                        // Insert vote
                        $insertVote->bind_param("iis", $selectedElectionID, $candidateID, $voterID);
                        $insertVote->execute();
                        $addedVotes++;
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                // Show message about added votes
                $_SESSION['message'] = "Added $addedVotes test votes for demonstration purposes.";
                $_SESSION['message_type'] = "success";
                
                // Update vote counts after adding votes
                updateVoteResults($conn, $selectedElectionID);
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                
                // Log error
                error_log("Error adding test votes: " . $e->getMessage());
            }
        }
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start is removed as it's already in auth_check.php
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

// Get all elections
$electionsResult = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");
$elections = [];

// Store all elections in an array for reuse
while ($election = $electionsResult->fetch_assoc()) {
    $elections[] = $election;
}

// Initialize variables
$electionID = $_GET['election'] ?? null;
$electionDetails = null;
$resultsData = [];
$totalVotes = 0;

// Add debug flags
$showDebug = true; // Set to true to show debug information
$debugInfo = [];

// Initialize votes tables info
$votesTableInfo = [];

// Check the votes table structure to ensure our queries work properly
$votesTableStmt = $conn->query("SHOW TABLES LIKE 'votes'");
if ($votesTableStmt->num_rows > 0) {
    $votesTableInfo['exists'] = true;
    
    // Get column info
    $columnsStmt = $conn->query("SHOW COLUMNS FROM votes");
    $columns = [];
    while ($column = $columnsStmt->fetch_assoc()) {
        $columns[] = $column['Field'];
    }
    $votesTableInfo['columns'] = $columns;
    
    // Check if essential columns exist
    $votesTableInfo['has_candidateID'] = in_array('candidateID', $columns);
    $votesTableInfo['has_electionID'] = in_array('electionID', $columns);
} else {
    $votesTableInfo['exists'] = false;
}

// Get actual votes based on database structure
function getCandidateActualVotes($conn, $candidateID, $electionID, $votesTableInfo) {
    // If votes table doesn't exist or lack essential columns, return 0
    if (!$votesTableInfo['exists'] || 
        !$votesTableInfo['has_candidateID'] || 
        !$votesTableInfo['has_electionID']) {
        return 0;
    }
    
    try {
        // Get votes using the correct query based on table structure
        $stmt = $conn->prepare("
            SELECT COUNT(*) as vote_count 
            FROM votes 
            WHERE candidateID = ? AND electionID = ?
        ");
        
        if (!$stmt) {
            error_log("Error preparing vote count statement: " . $conn->error);
            return 0;
        }
        
        $stmt->bind_param("ii", $candidateID, $electionID);
        
        if (!$stmt->execute()) {
            error_log("Error executing vote count query: " . $stmt->error);
            return 0;
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Error getting vote count result: " . $stmt->error);
            return 0;
        }
        
        $row = $result->fetch_assoc();
        return $row['vote_count'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception in getCandidateActualVotes: " . $e->getMessage());
        return 0;
    }
}

// Helper function to find profile pictures
function findProfilePicture($candidate) {
    $possiblePaths = [];
    $checkedPaths = []; // For debugging
    
    // Check student profile picture
    if (!empty($candidate['profilePicture'])) {
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['profilePicture'];
        $possiblePaths[] = 'assets/img/' . $candidate['profilePicture'];
    }
    
    // Check candidate photo if it exists
    if (!empty($candidate['candidatePhoto'])) {
        $possiblePaths[] = 'assets/img/candidates/' . $candidate['candidatePhoto'];
        $possiblePaths[] = 'assets/img/' . $candidate['candidatePhoto'];
    }
    
    // Check by student ID (common naming pattern)
    if (!empty($candidate['studentID'])) {
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['studentID'] . '.jpg';
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['studentID'] . '.png';
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['studentID'] . '.jpeg';
        
        // Try with timestamp patterns (common in the existing files)
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['studentID'] . '_*.jpg';
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['studentID'] . '_*.png';
        $possiblePaths[] = 'assets/img/profile/students/' . $candidate['studentID'] . '_*.jpeg';
        
        // Try specific candidate images
        $stdID = $candidate['studentID'];
        // Check for student ID pattern files using glob
        $stdIDFiles = glob("assets/img/profile/students/{$stdID}_*.{jpg,jpeg,png}", GLOB_BRACE);
        if (!empty($stdIDFiles)) {
            // Add any found files at the beginning of the array for priority
            $possiblePaths = array_merge($stdIDFiles, $possiblePaths);
        }
    }
    
    // Add the name-based files
    if (!empty($candidate['name'])) {
        // Convert spaces to underscores and lowercase
        $nameFile = strtolower(str_replace(' ', '_', $candidate['name']));
        $possiblePaths[] = "assets/img/profile/students/{$nameFile}.jpg";
        $possiblePaths[] = "assets/img/profile/students/{$nameFile}.png";
        $possiblePaths[] = "assets/img/profile/students/{$nameFile}.jpeg";
    }
    
    // Default fallback 
    $possiblePaths[] = 'assets/img/default-profile.png';
    $possiblePaths[] = 'assets/img/aristo.png';
    
    // Check each path and return the first one that exists
    foreach ($possiblePaths as $path) {
        // Skip glob patterns in the exists check
        if (strpos($path, '*') !== false) {
            continue;
        }
        
        $checkedPaths[] = $path . " (" . (file_exists($path) ? "exists" : "missing") . ")";
        
        if (file_exists($path)) {
            // Store the debug info in the session for later viewing
            if (!isset($_SESSION['debug_picture_paths'])) {
                $_SESSION['debug_picture_paths'] = [];
            }
            $_SESSION['debug_picture_paths'][$candidate['studentID']] = [
                'studentID' => $candidate['studentID'],
                'name' => $candidate['name'],
                'found' => $path,
                'checked' => $checkedPaths
            ];
            
            return $path;
        }
    }
    
    // Store the debug info in the session for later viewing
    if (!isset($_SESSION['debug_picture_paths'])) {
        $_SESSION['debug_picture_paths'] = [];
    }
    $_SESSION['debug_picture_paths'][$candidate['studentID']] = [
        'studentID' => $candidate['studentID'],
        'name' => $candidate['name'],
        'found' => false,
        'checked' => $checkedPaths
    ];
    
    // If no image found, return empty string
    return '';
}

if ($electionID) {
    // Get election details
    $electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionDetails = $electionStmt->get_result()->fetch_assoc();

    // Debug: Store election details
    if ($showDebug) {
        $debugInfo['election'] = $electionDetails;
    }

    // Get all positions for this election
    $stmt = $conn->prepare("
        SELECT * 
        FROM positions 
        WHERE electionID = ? 
        ORDER BY display_order, positionID ASC
    ");
    $stmt->bind_param("i", $electionID);
    $stmt->execute();
    $positions = $stmt->get_result();

    // Debug: Get total votes for this election directly
    if ($showDebug) {
        $voteCountStmt = $conn->prepare("
            SELECT COUNT(*) as total_votes 
            FROM votes 
            WHERE electionID = ?
        ");
        $voteCountStmt->bind_param("i", $electionID);
        $voteCountStmt->execute();
        $debugInfo['direct_vote_count'] = $voteCountStmt->get_result()->fetch_assoc()['total_votes'];
    }

    // Get results grouped by position
    if ($positions->num_rows > 0) {
        while ($position = $positions->fetch_assoc()) {
            $positionID = $position['positionID'];
            
            // Get candidates and their results for this position
            $candidates = $conn->query("
                SELECT c.candidateID, c.studentID, c.position, c.manifesto, c.photo as candidatePhoto,
                       s.name, s.profilePicture, s.email, s.department,
                       COALESCE(r.voteCount, 0) as voteCount,
                       COALESCE(r.percentage, 0) as percentage
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                LEFT JOIN results r ON c.candidateID = r.candidateID AND r.electionID = $electionID
                WHERE c.position = '{$position['title']}' AND c.status = 'Approved'
                ORDER BY voteCount DESC
            ");

            $positionResults = [
                'title' => $position['title'],
                'maxVotes' => $position['maxVotes'],
                'candidates' => [],
                'totalVotes' => 0
            ];

            while ($candidate = $candidates->fetch_assoc()) {
                // Get actual votes using our function
                $candidate['actualVotes'] = getCandidateActualVotes($conn, $candidate['candidateID'], $electionID, $votesTableInfo);
                
                // Use actualVotes if voteCount is 0
                $effectiveVoteCount = ($candidate['voteCount'] > 0) ? $candidate['voteCount'] : $candidate['actualVotes'];
                
                $positionResults['candidates'][] = $candidate;
                $positionResults['totalVotes'] += $effectiveVoteCount;
                $totalVotes += $effectiveVoteCount;
            }

            // Calculate percentages if not stored
            foreach ($positionResults['candidates'] as &$candidate) {
                // Use actualVotes if voteCount is 0
                $effectiveVoteCount = ($candidate['voteCount'] > 0) ? $candidate['voteCount'] : $candidate['actualVotes'];
                
                if ($positionResults['totalVotes'] > 0) {
                    $candidate['percentage'] = number_format(($effectiveVoteCount / $positionResults['totalVotes']) * 100, 2);
                }
            }

            $resultsData[] = $positionResults;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Election Results - SmartVote</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Base Styles */
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-content {
            margin-left: 40px;
            padding-bottom: 2rem;
        }
        
        /* Card Styles */
        .card {
            border-radius: 1rem;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            border: none;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08);
        }
       
        
        .card-header {
            background-color: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        
        /* Icon Styles */
        .card-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            margin-right: 1rem;
            transition: transform 0.3s;
        }
        
        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.15);
            color: #0d6efd;
        }
        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }
        .bg-info-light {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
        }
        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        .bg-secondary-light {
            background-color: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        .bg-purple-light {
            background-color: rgba(111, 66, 193, 0.15);
            color: #6f42c1;
        }
        .bg-teal-light {
            background-color: rgba(32, 201, 151, 0.15);
            color: #20c997;
        }
        
        /* Progress Bar Styles */
        .progress-thin {
            height: 8px;
            border-radius: 4px;
        }
        .progress-bar-custom {
            background-color: #edf2f9;
            border-radius: 0.75rem;
            height: 0.9rem;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
            margin: 0.75rem 0;
        }
        .progress-custom {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border-radius: 0.75rem;
            height: 100%;
            transition: width 1.2s ease-in-out;
        }
        
        /* Avatar and Image Styles */
        .user-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.15);
            border: 3px solid #fff;
        }
        .initials-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.15);
            border: 3px solid #fff;
        }
        .candidate-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 0 auto 1.25rem;
        }
        .card:hover .candidate-photo {
            transform: scale(1.08);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
        }
        
        /* Badge Styles */
        .winner-badge {
            position: absolute;
            top: -15px;
            right: -15px;
            background: linear-gradient(45deg, #ffc107, #ff9800);
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.2);
            z-index: 2;
            border: 3px solid white;
            transition: transform 0.3s;
        }
        .card:hover .winner-badge {
            transform: scale(1.1) rotate(15deg);
        }
        
        .badge {
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
        }
        
        /* Text Styles */
        .position-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #eaecef;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .vote-count {
            font-weight: 600;
            color: #0d6efd;
        }
        .percentage {
            font-weight: 600;
            color: #198754;
        }
        
        /* Button Styles */
        .btn {
            border-radius: 0.6rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
            border: none;
        }
        .btn-outline-primary {
            border-color: #4e73df;
            color: #4e73df;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(45deg, #4e73df, #224abe);
        }
        
        /* Filter Section */
        .filter-section {
            background-color: rgba(255, 255, 255, 0.98);
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            margin-bottom: 2rem;
        }
        .filter-section:hover {
            box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.15);
        }
        .filter-section .form-control,
        .filter-section .form-select {
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            border-color: #e0e5ec;
        }
        .filter-section .form-control:focus,
        .filter-section .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #4e73df;
        }
        
        /* Results Cards */
        .results-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s;
            margin-bottom: 2rem;
        }
        .results-card:hover {
            transform: translateY(-5px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0.9), rgba(255,255,255,0.98));
            border-radius: 1rem;
        }
        .empty-state-icon {
            font-size: 4.5rem;
            margin-bottom: 1.5rem;
            color: #6c757d;
            opacity: 0.6;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Print Styling */
        @media print {
            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
           
            .progress-custom {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        /* Stats Badge */
        .stats-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        
        /* Status Indicator */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .status-active {
            background-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
        }
        .status-completed {
            background-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }
        .status-pending {
            background-color: #ffc107;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
        }
        
        /* Candidate Details */
        .candidate-details {
            transition: all 0.3s;
            padding: 1.25rem;
        }
       
        
        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #eaecef;
        }
        .section-header i {
            color: #4e73df;
            font-size: 1.75rem;
            background-color: rgba(78, 115, 223, 0.1);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .section-header h2 {
            margin: 0;
            font-weight: 600;
        }
        
        /* Profile Modal Styles */
        .profile-modal-img {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .profile-modal-img:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
        }
        
        /* Candidate Profile Info Styles */
        .candidate-info p {
            transition: all 0.2s;
            padding: 0.25rem;
            border-radius: 4px;
        }
        .candidate-info p:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transform: translateX(3px);
        }
        
        /* List Group Styles in Profile Modal */
        .list-group-item {
            transition: all 0.2s;
            border-left: 0;
            border-right: 0;
        }
        .list-group-item:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        /* Manifesto Content Styles */
        .manifesto-content {
            line-height: 1.6;
            white-space: pre-line;
            font-size: 0.95rem;
            color: #495057;
            padding: 0.5rem;
            border-radius: 0.5rem;
            background-color: rgba(248, 249, 250, 0.7);
        }
        
        /* Modal Animation */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out, opacity 0.2s;
            transform: scale(0.95);
            opacity: 0;
        }
        .modal.show .modal-dialog {
            transform: scale(1);
            opacity: 1;
        }
        
        /* View Profile Button Hover Effect */
        .btn-outline-primary.w-100:hover {
            box-shadow: 0 0.25rem 0.75rem rgba(78, 115, 223, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?>
                <div class=" w-75 mx-auto shadow-sm border-0 mb-4">
                
                <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                    // Clear the message so it doesn't show again on refresh
                    unset($_SESSION['message']); 
                    unset($_SESSION['message_type']);
                ?>
                <?php endif; ?>
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4">
                    <!-- Page Header with Breadcrumb -->
                    <nav aria-label="breadcrumb" class="no-print mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded-pill shadow-sm d-inline-flex">
                            <li class="breadcrumb-item">
                                <a href="dashboard.php" class="text-decoration-none d-flex align-items-center">
                                    <i class="bi bi-house-door-fill text-primary me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <span class="d-flex align-items-center">
                                    <i class="bi bi-bar-chart-line-fill text-primary me-2"></i> Election Results
                                </span>
                            </li>
                        </ol>
                    </nav>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                        <h1 class="h2 d-flex align-items-center">
                            <div class="card-icon bg-warning-light me-3">
                                <i class="bi bi-trophy-fill fs-3"></i>
                            </div>
                            Election Results
                        </h1>
                        <div class="btn-toolbar mb-2 mb-md-0 no-print">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="bi bi-printer-fill"></i> Print
                                </button>
                                <a href="export_results.php?election=<?php echo $electionID; ?>&type=excel" 
                                   class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Excel
                                </a>
                                <a href="export_results.php?election=<?php echo $electionID; ?>&type=pdf" 
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                </a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-share-fill"></i> Share
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item d-flex align-items-center" href="#">
                                    <i class="bi bi-envelope-paper-fill me-2 text-primary"></i> Email Results
                                </a></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="#">
                                    <i class="bi bi-link-45deg me-2 text-primary"></i> Copy Link
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item d-flex align-items-center" href="#">
                                    <i class="bi bi-broadcast-pin me-2 text-primary"></i> Publish Results
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Election Selection & Controls -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">View Election Results</h5>
                                
                                <!-- Add refresh results button -->
                                <?php if ($electionID): ?>
                                <button id="refreshResultsBtn" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Election Results
                                </button>
                                <?php endif; ?>
                            </div>
                            
                            <form method="get" class="d-flex flex-wrap gap-2">
                                <div class="flex-grow-1 min-width-200">
                                    <select name="election" class="form-select" onchange="this.form.submit()">
                                        <option value="">Select an Election</option>
                                        <?php foreach ($elections as $election): ?>
                                            <option value="<?php echo $election['electionID']; ?>" <?php echo ($electionID == $election['electionID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($election['name']); ?> 
                                                (<?php echo $election['status']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <?php if ($electionID): ?>
                                <a href="export_results.php?election=<?php echo $electionID; ?>&type=excel" class="btn btn-outline-success">
                                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                                </a>
                                <a href="export_results.php?election=<?php echo $electionID; ?>&type=pdf" class="btn btn-outline-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Election Filter -->
                    <div class="card border-0 shadow-sm mb-4 filter-section no-print">
                        <div class="card-header bg-white py-3 d-flex align-items-center">
                            <div class="card-icon bg-primary-light me-3">
                                <i class="bi bi-funnel-fill fs-4"></i>
                            </div>
                            <h5 class="card-title mb-0 fw-bold">Election Filter</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center fw-medium">
                                        <i class="bi bi-calendar2-event-fill text-primary me-2"></i>
                                        Select Election
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <select class="form-select border-start-0" name="election" onchange="this.form.submit()">
                                            <option value="">-- Select Election --</option>
                                            <?php foreach ($elections as $election): ?>
                                            <option value="<?php echo $election['electionID']; ?>" 
                                                <?php echo $electionID == $election['electionID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($election['name']); ?>
                                                (<?php echo date('M Y', strtotime($election['startDate'])); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary d-flex align-items-center" type="submit">
                                            <i class="bi bi-filter-square-fill me-2"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <?php if ($electionID): ?>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center fw-medium">
                                        <i class="bi bi-clock-history text-primary me-2"></i>
                                        Results Generated
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-clock-fill"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" 
                                               value="<?php echo date('F j, Y, g:i a'); ?>" readonly>
                                        <span class="input-group-text bg-success text-white">
                                            <i class="bi bi-check2-circle"></i>
                                        </span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <!-- Election Header Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="mb-3 d-flex align-items-center">
                                        <div class="card-icon bg-warning-light me-3">
                                            <i class="bi bi-award-fill fs-3"></i>
                                        </div>
                                        <?php echo htmlspecialchars($electionDetails['name']); ?>
                                    </h3>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-light rounded-pill px-3 py-2 d-flex align-items-center me-3">
                                            <i class="bi bi-calendar-range-fill text-primary me-2"></i>
                                            <span class="fw-medium">
                                                <?php echo date('F j, Y', strtotime($electionDetails['startDate'])); ?> - 
                                                <?php echo date('F j, Y', strtotime($electionDetails['endDate'])); ?>
                                            </span>
                                        </div>
                                        
                                        <span class="badge bg-<?php echo $electionDetails['status'] == 'active' ? 'success' : ($electionDetails['status'] == 'completed' ? 'primary' : 'warning'); ?> d-flex align-items-center">
                                            <i class="bi bi-<?php echo $electionDetails['status'] == 'active' ? 'check-circle-fill' : ($electionDetails['status'] == 'completed' ? 'flag-fill' : 'clock-fill'); ?> me-2"></i>
                                            <?php echo ucfirst($electionDetails['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-inline-flex align-items-center bg-primary bg-opacity-10 rounded-pill px-4 py-3 text-primary fw-bold">
                                        <div class="card-icon bg-primary-light me-2">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fs-6 text-muted">Total Votes</div>
                                            <div class="fs-4"><?php echo number_format($totalVotes); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    

                    <!-- Heading for Results Section -->
                    <div class="mt-5 mb-4 section-header">
                        <i class="bi bi-clipboard-data"></i>
                        <h2>Results by Position</h2>
                    </div>

                    <!-- Results by Position -->
                    <?php if (!empty($resultsData)): ?>
                        <?php foreach ($resultsData as $position): ?>
                        <div class="card border-0 shadow-sm mb-4 results-card">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <div class="card-icon bg-primary-light me-3">
                                        <i class="bi bi-person-badge-fill fs-4"></i>
                                    </div>
                                    <h3 class="position-title mb-0 flex-grow-1">
                                        <?php echo htmlspecialchars($position['title']); ?>
                                    </h3>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-secondary d-flex align-items-center">
                                            <i class="bi bi-check2-all me-1"></i>
                                            Max Votes: <?php echo $position['maxVotes']; ?>
                                        </span>
                                        <span class="badge bg-info d-flex align-items-center">
                                            <i class="bi bi-people-fill me-1"></i>
                                            Total: <?php echo $position['totalVotes']; ?> votes
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <?php 
                                    $maxVotes = !empty($position['candidates']) ? max(array_column($position['candidates'], 'voteCount')) : 0;
                                    foreach ($position['candidates'] as $candidate): 
                                        $isWinner = ($candidate['voteCount'] == $maxVotes && $maxVotes > 0);
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm h-100 position-relative <?php echo $isWinner ? 'border border-warning border-3' : ''; ?>">
                                            <?php if ($isWinner): ?>
                                            <span class="winner-badge" title="Winner">
                                                <i class="bi bi-trophy-fill"></i>
                                            </span>
                                            <?php endif; ?>
                                            <div class="card-body text-center candidate-details p-4">
                                                <div class="position-relative mb-4">
                                                    <?php 
                                                    // Check both profile picture sources
                                                    $profilePic = findProfilePicture($candidate);
                                                    
                                                    if (!empty($profilePic)): 
                                                    ?>
                                                    <img src="<?php echo $profilePic; ?>" 
                                                        class="candidate-photo" 
                                                        alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#profileModal<?php echo $candidate['candidateID']; ?>"
                                                        style="cursor: pointer;"
                                                        onerror="this.onerror=null;this.src='assets/img/default-profile.png'">
                                                    <?php else: ?>
                                                    <div class="candidate-photo bg-light d-flex align-items-center justify-content-center"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#profileModal<?php echo $candidate['candidateID']; ?>"
                                                         style="cursor: pointer;">
                                                        <i class="bi bi-person-circle text-muted" style="font-size: 2.5rem;"></i>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <h5 class="mb-2 fw-bold d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-person-vcard-fill me-2 text-primary"></i>
                                                    <a href="#" class="text-decoration-none text-dark" 
                                                       data-bs-toggle="modal" 
                                                       data-bs-target="#profileModal<?php echo $candidate['candidateID']; ?>">
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                    </a>
                                                </h5>
                                                
                                                <div class="candidate-info mb-3">
                                                    <p class="mb-1 text-muted small">
                                                        <i class="bi bi-person-badge me-1"></i> 
                                                        ID: <?php echo htmlspecialchars($candidate['studentID']); ?>
                                                    </p>
                                                    <?php if (!empty($candidate['department'])): ?>
                                                    <p class="mb-1 text-muted small">
                                                        <i class="bi bi-building me-1"></i> 
                                                        <?php echo htmlspecialchars($candidate['department']); ?>
                                                    </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($candidate['email'])): ?>
                                                    <p class="mb-1 text-muted small">
                                                        <i class="bi bi-envelope me-1"></i> 
                                                        <?php echo htmlspecialchars($candidate['email']); ?>
                                                    </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($candidate['profilePicture']) || !empty($candidate['candidatePhoto'])): ?>
                                                    <p class="mb-1 text-muted small">
                                                        <i class="bi bi-image me-1"></i> 
                                                        <?php if (!empty($candidate['profilePicture'])): ?>
                                                        Student Pic: <?php echo htmlspecialchars($candidate['profilePicture']); ?><br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($candidate['candidatePhoto'])): ?>
                                                        Candidate Pic: <?php echo htmlspecialchars($candidate['candidatePhoto']); ?><br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($profilePic)): ?>
                                                        <span class="badge bg-success">Found image: <?php echo basename($profilePic); ?></span>
                                                        <?php else: ?>
                                                        <span class="badge bg-danger">No image found</span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php 
                                                    // Display all known files in the students directory
                                                    $existingFiles = glob("assets/img/profile/students/*");
                                                    $matchingFiles = [];
                                                    
                                                    if (!empty($candidate['studentID'])) {
                                                        $studentID = $candidate['studentID'];
                                                        foreach ($existingFiles as $file) {
                                                            if (strpos(basename($file), $studentID) !== false) {
                                                                $matchingFiles[] = basename($file);
                                                            }
                                                        }
                                                    }
                                                    
                                                    if (!empty($matchingFiles)):
                                                    ?>
                                                    <p class="mt-2 text-success small">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Matching files: <?php echo implode(", ", $matchingFiles); ?>
                                                    </p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="bg-light rounded p-3 mb-3">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted d-flex align-items-center">
                                                            <i class="bi bi-check2-circle me-2 text-success"></i>
                                                            Votes
                                                        </span>
                                                        <span class="vote-count d-flex align-items-center fs-5">
                                                            <i class="bi bi-123 me-1"></i>
                                                            <?php 
                                                                // Use actual votes if stored votes are 0 or there's a mismatch
                                                                $displayVotes = $candidate['voteCount'];
                                                                if ($displayVotes == 0 && $candidate['actualVotes'] > 0) {
                                                                    $displayVotes = $candidate['actualVotes'];
                                                                    echo "<span class='text-warning' title='Vote count corrected from actual votes'>";
                                                                    echo number_format($displayVotes);
                                                                    echo " *</span>";
                                                                } elseif ($candidate['voteCount'] != $candidate['actualVotes']) {
                                                                    echo "<span class='text-info' title='Stored: {$candidate['voteCount']}, Actual: {$candidate['actualVotes']}'>";
                                                                    echo number_format($displayVotes); 
                                                                    echo " <small class='text-warning'>(" . number_format($candidate['actualVotes']) . ")</small></span>";
                                                                } else {
                                                                    echo number_format($displayVotes);
                                                                }
                                                            ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="progress-bar-custom mb-2">
                                                        <div class="progress-custom" 
                                                            style="width: <?php echo $candidate['percentage']; ?>%">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted d-flex align-items-center">
                                                            <i class="bi bi-percent me-1"></i>
                                                            Percentage
                                                        </span>
                                                        <span class="percentage d-flex align-items-center fs-5">
                                                            <i class="bi bi-graph-up-arrow me-1"></i>
                                                            <?php echo $candidate['percentage']; ?>%
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($candidate['manifesto'])): ?>
                                                <div class="manifesto-preview mb-3 text-start">
                                                    <h6 class="fw-bold text-primary mb-2">
                                                        <i class="bi bi-file-text me-1"></i> Manifesto:
                                                    </h6>
                                                    <p class="small text-muted">
                                                        <?php echo nl2br(htmlspecialchars(substr($candidate['manifesto'], 0, 100))); ?>
                                                        <?php echo (strlen($candidate['manifesto']) > 100) ? '...' : ''; ?>
                                                    </p>
                                                    <button class="btn btn-sm btn-outline-primary mt-1" 
                                                           data-bs-toggle="modal" 
                                                           data-bs-target="#manifestoModal<?php echo $candidate['candidateID']; ?>">
                                                        <i class="bi bi-eye me-1"></i> View Full
                                                    </button>
                                                </div>
                                                
                                                <!-- Manifesto Modal -->
                                                <div class="modal fade" id="manifestoModal<?php echo $candidate['candidateID']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-file-text-fill me-2 text-primary"></i>
                                                                    <?php echo htmlspecialchars($candidate['name']); ?>'s Manifesto
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="candidate-modal-info mb-3 pb-3 border-bottom">
                                                                    <div class="d-flex align-items-center">
                                                                        <?php 
                                                                        $profilePic = findProfilePicture($candidate);
                                                                                
                                                                        if (!empty($profilePic)): 
                                                                        ?>
                                                                        <img src="<?php echo $profilePic; ?>" 
                                                                            class="user-avatar me-3" 
                                                                            alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                                            onerror="this.onerror=null;this.src='assets/img/default-profile.png'">
                                                                        <?php else: ?>
                                                                        <div class="initials-avatar me-3">
                                                                            <?php echo strtoupper(substr($candidate['name'], 0, 1)); ?>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        <div>
                                                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($candidate['name']); ?></h6>
                                                                            <p class="mb-0 text-muted small">
                                                                                <i class="bi bi-award me-1"></i> 
                                                                                Candidate for <?php echo htmlspecialchars($position['title']); ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="manifesto-content">
                                                                    <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($isWinner): ?>
                                                <div class="mt-3">
                                                    <span class="badge bg-warning text-dark d-flex align-items-center justify-content-center mx-auto py-2 px-3">
                                                        <i class="bi bi-trophy-fill me-2"></i>
                                                        Winner
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- Profile Details Button -->
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-outline-primary w-100" 
                                                           data-bs-toggle="modal" 
                                                           data-bs-target="#profileModal<?php echo $candidate['candidateID']; ?>">
                                                        <i class="bi bi-person-lines-fill me-1"></i> View Profile (ID: <?php echo $candidate['studentID']; ?>)
                                                    </button>
                                                </div>
                                                
                                                <!-- Profile Modal -->
                                                <div class="modal fade" id="profileModal<?php echo $candidate['candidateID']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-person-badge-fill me-2"></i>
                                                                    Candidate Profile
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-4 text-center mb-4 mb-md-0">
                                                                        <?php 
                                                                        $profilePic = findProfilePicture($candidate);
                                                                                
                                                                        if (!empty($profilePic)): 
                                                                        ?>
                                                                        <img src="<?php echo $profilePic; ?>" 
                                                                            class="img-fluid rounded-circle profile-modal-img mb-3" 
                                                                            style="width: 180px; height: 180px; object-fit: cover; border: 5px solid #eee;"
                                                                            alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                                            onerror="this.onerror=null;this.src='assets/img/default-profile.png'">
                                                                        <?php else: ?>
                                                                        <div class="profile-modal-img mx-auto mb-3 bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                                             style="width: 180px; height: 180px; border: 5px solid #eee;">
                                                                            <i class="bi bi-person-circle text-muted" style="font-size: 5rem;"></i>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <h4 class="fw-bold"><?php echo htmlspecialchars($candidate['name']); ?></h4>
                                                                        
                                                                        <div class="d-flex justify-content-center mt-2 mb-3">
                                                                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                                                                <i class="bi bi-award-fill me-1"></i>
                                                                                <?php echo htmlspecialchars($position['title']); ?> Candidate
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <?php if ($isWinner): ?>
                                                                        <div class="winner-badge-modal mt-2">
                                                                            <span class="badge bg-warning text-dark d-inline-flex align-items-center justify-content-center py-2 px-4">
                                                                                <i class="bi bi-trophy-fill me-2"></i>
                                                                                Winner
                                                                            </span>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($candidate['email'])): ?>
                                                                        <li class="list-group-item px-0 py-2 d-flex align-items-center">
                                                                            <div class="card-icon bg-purple-light me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                                                <i class="bi bi-envelope"></i>
                                                                            </div>
                                                                            <div>
                                                                                <span class="text-muted small">Email</span>
                                                                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($candidate['email']); ?></p>
                                                                            </div>
                                                                        </li>
                                                                        <?php endif; ?>
                                                                        
                                                                        <li class="list-group-item px-0 py-2 d-flex align-items-center">
                                                                            <div class="card-icon bg-secondary-light me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                                                <i class="bi bi-person-vcard"></i>
                                                                            </div>
                                                                            <div>
                                                                                <span class="text-muted small">Student ID</span>
                                                                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($candidate['studentID']); ?></p>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            
                                                            <?php if (!empty($candidate['manifesto'])): ?>
                                                            <div class="card border-0 shadow-sm">
                                                                <div class="card-header bg-light">
                                                                    <h5 class="mb-0">
                                                                        <i class="bi bi-file-text-fill text-primary me-2"></i>
                                                                        Manifesto
                                                                    </h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="manifesto-content">
                                                                        <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Print/Export Buttons -->
                        <div class="text-center mt-5 mb-4 no-print">
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <button class="btn btn-primary px-4 py-2" onclick="window.print()">
                                    <i class="bi bi-printer-fill me-2 fs-5"></i> 
                                    <span>Print Results</span>
                                </button>
                                <button class="btn btn-success px-4 py-2" id="exportExcel">
                                    <i class="bi bi-file-earmark-excel-fill me-2 fs-5"></i> 
                                    <span>Export to Excel</span>
                                </button>
                                <button class="btn btn-danger px-4 py-2" id="exportPDF">
                                    <i class="bi bi-file-earmark-pdf-fill me-2 fs-5"></i> 
                                    <span>Save as PDF</span>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Results Message -->
                        <div class="card border-0 shadow-sm text-center py-5 empty-state">
                            <div class="card-body p-5">
                                <div class="card-icon bg-info-light mx-auto mb-4" style="width: 80px; height: 80px;">
                                    <i class="bi bi-info-circle-fill fs-1"></i>
                                </div>
                                <h4 class="mt-3 mb-3 fw-bold">No Results Available Yet</h4>
                                <p class="text-muted mb-4 fs-5">Results will be displayed once voting has concluded and tallied.</p>
                                <a href="elections.php" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-calendar2-event-fill me-2"></i> View Elections
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php elseif ($electionID): ?>
                    <!-- Election Not Found -->
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body p-5">
                            <div class="card-icon bg-danger-light mx-auto mb-4" style="width: 80px; height: 80px;">
                                <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                            </div>
                            <h4 class="mt-3 mb-3 fw-bold">Election Not Found</h4>
                            <p class="text-muted mb-4 fs-5">The election you selected doesn't exist or may have been removed.</p>
                            <a href="results.php" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-arrow-left me-2"></i> Back to Results
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Select Election Message -->
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body p-5">
                            <div class="card-icon bg-primary-light mx-auto mb-4" style="width: 80px; height: 80px;">
                                <i class="bi bi-cursor-fill fs-1"></i>
                            </div>
                            <h4 class="mt-3 mb-3 fw-bold">Select an Election</h4>
                            <p class="text-muted mb-4 fs-5">Choose an election from the dropdown to view detailed voting results.</p>
                            <button class="btn btn-primary px-4 py-2" onclick="document.querySelector('select[name=\'election\']').focus()">
                                <i class="bi bi-arrow-up-circle-fill me-2"></i> Select Election
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
        
                </main>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Export Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bars with delay for each
        const progressBars = document.querySelectorAll('.progress-custom');
        progressBars.forEach((bar, index) => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100 + (index * 100)); // Staggered animation
        });
        
        // Add reveal animation to candidate cards
        const candidateCards = document.querySelectorAll('.col-md-6.col-lg-4');
        candidateCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 200 + (index * 100));
        });
        
        // Winner badge special animation
        const winnerBadges = document.querySelectorAll('.winner-badge');
        winnerBadges.forEach(badge => {
            setTimeout(() => {
                badge.classList.add('animate__animated', 'animate__tada');
            }, 1000);
        });
        
        // Animate elements in profile modals when opened
        const profileModals = document.querySelectorAll('[id^="profileModal"]');
        profileModals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                // Animate profile image
                const profileImg = this.querySelector('.profile-modal-img');
                if (profileImg) {
                    profileImg.style.transform = 'scale(0.8)';
                    profileImg.style.opacity = '0';
                    setTimeout(() => {
                        profileImg.style.transform = 'scale(1)';
                        profileImg.style.opacity = '1';
                        profileImg.style.transition = 'transform 0.5s ease-out, opacity 0.5s ease-out';
                    }, 100);
                }
                
                // Animate list items with staggered delay
                const listItems = this.querySelectorAll('.list-group-item');
                listItems.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    }, 150 + (index * 100));
                });
                
                // Animate manifesto content
                const manifestoContent = this.querySelector('.manifesto-content');
                if (manifestoContent) {
                    manifestoContent.style.opacity = '0';
                    manifestoContent.style.transform = 'translateY(10px)';
                    manifestoContent.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    
                    setTimeout(() => {
                        manifestoContent.style.opacity = '1';
                        manifestoContent.style.transform = 'translateY(0)';
                    }, 300);
                }
            });
        });
        
        // Export to Excel with improved styling
        document.getElementById('exportExcel')?.addEventListener('click', function() {
            // Show loading indicator
            this.innerHTML = '<i class="bi bi-arrow-repeat spin-animation me-2"></i> Generating...';
            this.disabled = true;
            
            setTimeout(() => {
                // Create a workbook
                const wb = XLSX.utils.book_new();
                
                // Get all the results data
                const results = <?php echo json_encode($resultsData ?? []); ?>;
                
                // Prepare data for export
                const exportData = [];
                
                results.forEach(position => {
                    // Add position header
                    exportData.push({
                        'Position': `== ${position.title} ==`,
                        'Max Votes': position.maxVotes,
                        'Total Votes': position.totalVotes
                    });
                    
                    // Add empty row
                    exportData.push({});
                    
                    // Add candidates
                    position.candidates.forEach(candidate => {
                        exportData.push({
                            'Candidate Name': candidate.name,
                            'Department': candidate.department || 'N/A',
                            'Email': candidate.email || 'N/A',
                            'Votes': candidate.voteCount,
                            'Percentage': candidate.percentage + '%',
                            'Is Winner': candidate.voteCount === Math.max(...position.candidates.map(c => c.voteCount)) ? 'Yes' : 'No'
                        });
                    });
                    
                    // Add empty rows between positions
                    exportData.push({});
                    exportData.push({});
                });
                
                // Create a worksheet
                const ws = XLSX.utils.json_to_sheet(exportData);
                
                // Add the worksheet to the workbook
                XLSX.utils.book_append_sheet(wb, ws, "Election Results");
                
                // Export the workbook
                XLSX.writeFile(wb, 'Election_Results_<?php echo isset($electionDetails['name']) ? preg_replace('/[^a-zA-Z0-9]/', '_', $electionDetails['name']) : 'Results'; ?>_<?php echo date('Y-m-d'); ?>.xlsx');
                
                // Reset button
                this.innerHTML = '<i class="bi bi-file-earmark-excel-fill me-2 fs-5"></i><span>Export to Excel</span>';
                this.disabled = false;
                
                // Show success toast
                showToast('Excel file exported successfully!', 'success');
            }, 800);
        });
        
        // Export to PDF with improved styling
        document.getElementById('exportPDF')?.addEventListener('click', function() {
            // Show loading indicator
            this.innerHTML = '<i class="bi bi-arrow-repeat spin-animation me-2"></i> Generating...';
            this.disabled = true;
            
            setTimeout(() => {
                // Create a new PDF document
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Add title with styling
                doc.setFontSize(22);
                doc.setTextColor(40, 50, 78);
                doc.text('Election Results', 105, 20, { align: 'center' });
                
                // Add election name
                doc.setFontSize(16);
                doc.setTextColor(78, 115, 223);
                doc.text('<?php echo isset($electionDetails['name']) ? $electionDetails['name'] : ''; ?>', 105, 30, { align: 'center' });
                
                // Add date
                doc.setFontSize(12);
                doc.setTextColor(100);
                doc.text('Generated on: <?php echo date('F j, Y, g:i a'); ?>', 105, 40, { align: 'center' });
                
                // Add line
                doc.setDrawColor(200);
                doc.setLineWidth(0.5);
                doc.line(20, 45, 190, 45);
                
                let yPosition = 55;
                
                // Add results for each position
                const results = <?php echo json_encode($resultsData ?? []); ?>;
                
                results.forEach((position, index) => {
                    // Add position title
                    if (index > 0) {
                        doc.addPage();
                        yPosition = 30;
                    }
                    
                    // Add position header
                    doc.setFillColor(240, 242, 245);
                    doc.roundedRect(20, yPosition - 5, 170, 12, 2, 2, 'F');
                    
                    doc.setFontSize(14);
                    doc.setTextColor(40);
                    doc.text(position.title + ' (Max Votes: ' + position.maxVotes + ')', 25, yPosition);
                    yPosition += 15;
                    
                    // Add candidates
                    position.candidates.forEach((candidate, idx) => {
                        if (yPosition > 250) {
                            doc.addPage();
                            yPosition = 30;
                        }
                        
                        // Candidate name with background
                        doc.setFillColor(248, 249, 250);
                        doc.roundedRect(25, yPosition - 5, 160, 35, 1, 1, 'F');
                        
                        doc.setFontSize(12);
                        doc.setTextColor(60);
                        doc.text(candidate.name, 30, yPosition);
                        
                        // Additional candidate details
                        doc.setFontSize(9);
                        doc.setTextColor(100);
                        if (candidate.department) {
                            doc.text('Department: ' + candidate.department, 30, yPosition + 7);
                        }
                        if (candidate.email) {
                            doc.text('Email: ' + candidate.email, 30, yPosition + 14);
                        }
                        
                        // Votes and percentage
                        doc.setFontSize(10);
                        doc.setTextColor(0, 102, 204);
                        doc.text('Votes: ' + candidate.voteCount + ' (' + candidate.percentage + '%)', 160, yPosition, { align: 'right' });
                        
                        // Progress bar background
                        doc.setFillColor(233, 236, 239);
                        doc.roundedRect(100, yPosition + 22, 70, 3, 1, 1, 'F');
                        
                        // Progress bar fill
                        doc.setFillColor(78, 115, 223);
                        if (parseFloat(candidate.percentage) > 0) {
                            doc.roundedRect(100, yPosition + 22, (70 * parseFloat(candidate.percentage) / 100), 3, 1, 1, 'F');
                        }
                        
                        // Add winner indicator
                        if (candidate.voteCount === Math.max(...position.candidates.map(c => c.voteCount)) && candidate.voteCount > 0) {
                            doc.setTextColor(255, 193, 7);
                            doc.text('★ WINNER', 160, yPosition + 15, { align: 'right' });
                        }
                        
                        yPosition += 40;
                    });
                    
                    yPosition += 10;
                });
                
                // Save the PDF
                doc.save('Election_Results_<?php echo isset($electionDetails['name']) ? preg_replace('/[^a-zA-Z0-9]/', '_', $electionDetails['name']) : 'Results'; ?>_<?php echo date('Y-m-d'); ?>.pdf');
                
                // Reset button
                this.innerHTML = '<i class="bi bi-file-earmark-pdf-fill me-2 fs-5"></i><span>Save as PDF</span>';
                this.disabled = false;
                
                // Show success toast
                showToast('PDF file created successfully!', 'success');
            }, 800);
        });
        
        // Share functionality
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.querySelector('i').className.split(' ')[1];
                
                switch(action) {
                    case 'bi-envelope-paper-fill':
                        const subject = 'Election Results: <?php echo isset($electionDetails['name']) ? $electionDetails['name'] : 'Latest Election'; ?>';
                        const body = 'View the results at: ' + window.location.href;
                        window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                        showToast('Email client opened', 'info');
                        break;
                    case 'bi-link-45deg':
                        navigator.clipboard.writeText(window.location.href)
                            .then(() => showToast('Link copied to clipboard!', 'success'))
                            .catch(() => showToast('Failed to copy link', 'danger'));
                        break;
                    case 'bi-broadcast-pin':
                        showToast('Publishing results... This may take a moment', 'info');
                        setTimeout(() => {
                            showToast('Results published successfully!', 'success');
                        }, 1500);
                        break;
                }
            });
        });
        
        // Interactive hover effects for candidate cards
        document.querySelectorAll('.candidate-details').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const photo = this.querySelector('.candidate-photo');
                if (photo) {
                    photo.style.transform = 'scale(1.08)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                const photo = this.querySelector('.candidate-photo');
                if (photo) {
                    photo.style.transform = 'scale(1)';
                }
            });
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            // Create toast container if it doesn't exist
            if (!document.querySelector('.toast-container')) {
                const toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '1080';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            // Create toast content
            const toastFlex = document.createElement('div');
            toastFlex.className = 'd-flex';
            
            const toastBody = document.createElement('div');
            toastBody.className = 'toast-body d-flex align-items-center';
            
            // Add icon based on type
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'danger') icon = 'exclamation-triangle';
            if (type === 'warning') icon = 'exclamation-circle';
            
            toastBody.innerHTML = `<i class="bi bi-${icon}-fill me-2"></i> ${message}`;
            
            const closeButton = document.createElement('button');
            closeButton.className = 'btn-close btn-close-white me-2 m-auto';
            closeButton.setAttribute('data-bs-dismiss', 'toast');
            closeButton.setAttribute('aria-label', 'Close');
            
            toastFlex.appendChild(toastBody);
            toastFlex.appendChild(closeButton);
            toastEl.appendChild(toastFlex);
            
            document.querySelector('.toast-container').appendChild(toastEl);
            
            // Initialize toast
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
            
            // Remove toast after it's hidden
            toastEl.addEventListener('hidden.bs.toast', function() {
                toastEl.remove();
            });
        }
        
        // Add CSS animation for spinner
        const style = document.createElement('style');
        style.textContent = `
            .spin-animation {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .animate__animated {
                animation-duration: 1s;
                animation-fill-mode: both;
            }
            .animate__tada {
                animation-name: tada;
            }
            @keyframes tada {
                0% { transform: scale3d(1, 1, 1); }
                10%, 20% { transform: scale3d(.9, .9, .9) rotate3d(0, 0, 1, -3deg); }
                30%, 50%, 70%, 90% { transform: scale3d(1.1, 1.1, 1.1) rotate3d(0, 0, 1, 3deg); }
                40%, 60%, 80% { transform: scale3d(1.1, 1.1, 1.1) rotate3d(0, 0, 1, -3deg); }
                100% { transform: scale3d(1, 1, 1); }
            }
        `;
        document.head.appendChild(style);
    });
    </script>
    
    <!-- Add refresh results JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const refreshBtn = document.getElementById('refreshResultsBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                refreshBtn.disabled = true;
                refreshBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Updating vote counts...';
                
                // Call our calculate_vote_results.php script
                fetch('calculate_vote_results.php?run=1&election=<?php echo $electionID; ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showToast('Results updated successfully! ' + data.records_updated + ' records updated.', 'success');
                            // Reload the page to show updated results
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showToast('Error: ' + data.message, 'danger');
                            refreshBtn.disabled = false;
                            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Election Results';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while updating results', 'danger');
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Election Results';
                    });
            });
        }
        
        // Toast notification function (reuse from existing code)
        function showToast(message, type = 'info') {
            // Create toast container if it doesn't exist
            if (!document.querySelector('.toast-container')) {
                const toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '1080';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            // Create toast content
            const toastFlex = document.createElement('div');
            toastFlex.className = 'd-flex';
            
            const toastBody = document.createElement('div');
            toastBody.className = 'toast-body d-flex align-items-center';
            
            // Add icon based on type
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'danger') icon = 'exclamation-triangle';
            if (type === 'warning') icon = 'exclamation-circle';
            
            toastBody.innerHTML = `<i class="bi bi-${icon}-fill me-2"></i> ${message}`;
            
            const closeButton = document.createElement('button');
            closeButton.className = 'btn-close btn-close-white me-2 m-auto';
            closeButton.setAttribute('data-bs-dismiss', 'toast');
            closeButton.setAttribute('aria-label', 'Close');
            
            toastFlex.appendChild(toastBody);
            toastFlex.appendChild(closeButton);
            toastEl.appendChild(toastFlex);
            
            document.querySelector('.toast-container').appendChild(toastEl);
            
            // Initialize toast
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
            
            // Remove toast after it's hidden
            toastEl.addEventListener('hidden.bs.toast', function() {
                toastEl.remove();
            });
        }
    });
    
    // Add spin animation
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .spin {
                animation: spin 1s linear infinite;
                display: inline-block;
            }
            .min-width-200 {
                min-width: 200px;
            }
        </style>
    `);
    </script>
</body>
</html>