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
$electionID = isset($_GET['election']) ? (int)$_GET['election'] : 0;
$currentElection = null;
$positions = [];
$error = null;

try {
    // If no election ID is provided, find the latest ongoing or completed election
    if ($electionID === 0) {
        $stmt = $conn->prepare("
            SELECT * FROM elections 
            WHERE status IN ('Ongoing', 'Completed')
            ORDER BY CASE 
                WHEN status = 'Ongoing' THEN 1 
                WHEN status = 'Completed' THEN 2 
                ELSE 3 
            END, startDate DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $currentElection = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($currentElection) {
            $electionID = (int)$currentElection['electionID'];
        }
    }
    
    // If we have an election ID (either from URL or found above), fetch its details
    if ($electionID > 0) {
        $stmt = $conn->prepare("
            SELECT * FROM elections 
            WHERE electionID = ?
        ");
        $stmt->bind_param('i', $electionID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $currentElection = $result->fetch_assoc();
        }
        $stmt->close();
    }

    if (!$currentElection) {
        $error = "Election not found.";
    } else {
        // 1. Fetch all positions for the election first
        $stmt = $conn->prepare("
            SELECT p.* 
            FROM positions p
            WHERE p.electionID = ?
            ORDER BY p.display_order ASC, p.positionID ASC
        ");
        $stmt->bind_param('i', $electionID);
        $stmt->execute();
        $all_positions_raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // 2. Deduplicate positions by title (case-insensitive), keeping the first encountered
        $uniquePositionsData = [];
        $seenPositionTitles = [];
        foreach ($all_positions_raw as $position_raw) {
            $lowerTitle = strtolower($position_raw['title']);
            if (!isset($seenPositionTitles[$lowerTitle])) {
                $seenPositionTitles[$lowerTitle] = true; // Mark title as seen
                // Add position data, initialize candidates array
                $uniquePositionsData[] = [
                    'positionID' => $position_raw['positionID'],
                    'title' => $position_raw['title'],
                    'description' => $position_raw['description'],
                    'maxVotes' => $position_raw['maxVotes'],
                    'display_order' => $position_raw['display_order'],
                    'candidates' => [], // Initialize empty candidates array
                    'totalVotes' => 0   // Initialize total votes
                ];
            }
        }
        $positions = $uniquePositionsData; // Use the deduplicated list

        // Debug: Log unique positions
        error_log("Unique positions count after deduplication: " . count($positions));
        foreach ($positions as $pos) {
            error_log("Unique Position Processed: {$pos['title']} (ID: {$pos['positionID']})");
        }

        // 3. Fetch candidates and calculate votes for each unique position
        foreach ($positions as &$position) { // Use reference to modify the array directly
            $currentPositionID = $position['positionID']; // Store the correct ID for this iteration
            error_log("Fetching candidates for: {$position['title']} (ID: {$currentPositionID})");

            $stmt = $conn->prepare("
                SELECT 
                    c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                    s.name, s.department, s.profilePicture,
                    IFNULL((SELECT COUNT(*) FROM votes WHERE candidateID = c.candidateID AND electionID = ?), 0) as voteCount
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.positionID = ? AND c.status = 'Approved' -- Filter by the correct positionID for this iteration
                GROUP BY c.candidateID 
                ORDER BY voteCount DESC, s.name ASC
            ");
            // Bind parameters: electionID for subquery, currentPositionID for WHERE clause
            $stmt->bind_param('ii', $electionID, $currentPositionID); 
            $stmt->execute();
            $position['candidates'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            error_log("Candidates found for {$position['title']}: " . count($position['candidates']));

            // Calculate total votes and percentages for this position
            $totalVotes = 0;
            foreach ($position['candidates'] as $candidate) {
                $totalVotes += (int)$candidate['voteCount'];
                error_log("  Candidate: {$candidate['name']} (ID: {$candidate['candidateID']}), Votes: {$candidate['voteCount']}");
            }
            $position['totalVotes'] = $totalVotes;

            foreach ($position['candidates'] as &$candidate) { // Use reference
                $candidate['votePercentage'] = $totalVotes > 0 ?
                    round(((int)$candidate['voteCount'] / $totalVotes) * 100, 1) : 0;
            }
            unset($candidate); // Unset inner loop reference
        }
        unset($position); // Unset outer loop reference

        // Debugging: Log final candidate-to-position mapping
        foreach ($positions as $position) {
            error_log("Position: {$position['title']} (ID: {$position['positionID']})");
            foreach ($position['candidates'] as $candidate) {
                error_log("Candidate: {$candidate['name']} (ID: {$candidate['candidateID']}) mapped to Position ID: {$position['positionID']}");
            }
        }

        // Get total votes for the election
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT studentID) as totalVoters
            FROM votes
            WHERE electionID = ?
        ");
        $stmt->bind_param('i', $electionID);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $totalVoters = $result['totalVoters'];
        $stmt->close();

        // Get total eligible voters
        $stmt = $conn->prepare("
            SELECT COUNT(*) as eligibleVoters
            FROM students
            WHERE status = 'Active'
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $eligibleVoters = $result['eligibleVoters'];
        $stmt->close();

        // Calculate voter turnout percentage
        $voterTurnout = $eligibleVoters > 0 ? 
            round(($totalVoters / $eligibleVoters) * 100, 1) : 0;
    }
} catch (Exception $e) {
    error_log("Results fetch error: " . $e->getMessage());
    $error = "Error loading election results.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Results - SmartVote</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
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
        
        /* Dark mode variables */
        [data-bs-theme="dark"] {
            --primary: #6ea8fe;
            --primary-light: rgba(110, 168, 254, 0.08);
            --primary-dark: #4361ee;
            --success: #75b798;
            --success-light: rgba(117, 183, 152, 0.1);
            --surface: #2b3035;
            --surface-hover: #343a40;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            --card-hover-shadow: 0 15px 35px rgba(110, 168, 254, 0.2);
            --text: #f8f9fa;
            --text-muted: #adb5bd;
            --border: #495057;
            --bg: #212529;
        }
        
        body {
            background-color: var(--bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text);
            line-height: 1.5;
        }
        
        .results-card {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
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
        
        .candidate-result {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-radius: 16px;
            background-color: var(--surface);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        
        .candidate-result.leading {
            border-left: 4px solid var(--success);
        }
        
        .avatar-container {
            width: 100px;
            height: 100px;
            margin-right: 1.5rem;
            position: relative;
            flex-shrink: 0;
            z-index: 2;
        }
        
        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--border);
            transition: all 0.4s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .candidate-result:hover .avatar {
            border-color: var(--primary-light);
            transform: scale(1.05);
        }
        
        .department-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: var(--surface);
            border: 1px solid var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 3;
        }
        
        .candidate-info {
            flex-grow: 1;
        }
        
        .candidate-name {
            font-weight: 700;
            margin-bottom: 0.2rem;
            font-size: 1.1rem;
        }
        
        .candidate-position {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.75rem;
            margin-bottom: 0.3rem;
        }
        
        .vote-count {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
            margin-right: 0.5rem;
        }
        
        .vote-percentage {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: var(--border);
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 4px;
            transition: width 1.5s ease-in-out;
        }
        
        .leading .progress-bar {
            background: linear-gradient(135deg, var(--success) 0%, #0d9a6b 100%);
        }
        
        .stats-card {
            background: var(--surface);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(67, 97, 238, 0.3);
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .stats-icon.blue {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .stats-icon.green {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .stats-icon.purple {
            background-color: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }
        
        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-family: 'DM Mono', monospace;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        
        .refresh-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }
        
        .refresh-btn i {
            transition: transform 0.5s ease;
        }
        
        .refresh-btn:hover i {
            transform: rotate(180deg);
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            margin-left: 1rem;
        }
        
        .live-indicator::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }
        
        .election-timer {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .counter-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .timer-countdown {
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'DM Mono', monospace;
        }
        
        .progress-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .progress-wave:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            animation: wave 3s linear infinite;
        }
        
        @keyframes wave {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(0%); }
        }
        
        .winner-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--success);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
            z-index: 2;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 99;
            cursor: pointer;
        }
        
        .back-to-top.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }
        
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .candidate-result {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .avatar-container {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .vote-count {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }
            
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
            }
        }
        
        .alert.bg-light {
            background-color: var(--surface) !important;
            color: var(--text);
        }
        
        .card-header {
            background-color: var(--surface) !important;
            color: var(--text);
        }

        [data-bs-theme="dark"] .bg-white {
            background-color: var(--surface) !important;
        }

        [data-bs-theme="dark"] .avatar.bg-primary {
            background-color: rgba(110, 168, 254, 0.2) !important;
        }

        [data-bs-theme="dark"] .text-muted {
            color: var(--text-muted) !important;
        }

        [data-bs-theme="dark"] .position-section {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        [data-bs-theme="dark"] .btn-outline-primary:hover {
            background-color: var(--primary-dark);
            color: #ffffff;
        }

        [data-bs-theme="dark"] .counter-circle.bg-secondary {
            background-color: rgba(173, 181, 189, 0.2) !important;
        }

        /* Fix refresh indicator in dark mode */
        [data-bs-theme="dark"] .refresh-indicator {
            background-color: rgba(110, 168, 254, 0.9) !important;
            color: #ffffff !important;
        }

        /* Fix alert backgrounds */
        [data-bs-theme="dark"] .alert-success {
            background-color: rgba(117, 183, 152, 0.2);
            color: #d1e7dd;
            border-color: rgba(117, 183, 152, 0.4);
        }

        [data-bs-theme="dark"] .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border-color: rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>
   
    <?php include 'includes/header.php'; ?><br>
   
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12 col-sm-12">
                <div class="results-card mb-4">
                    <div class="card-header bg-white py-4 px-4 border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="mb-3 mb-md-0">
                                <h2 class="mb-1 fw-bold"><i class="bi bi-bar-chart-fill role-icon icon text-primary"></i>&nbsp;Live Results</h2>
                                <p class="text-muted mb-0"><i class="bi bi-calendar-check me-2"></i>Real-time voting results for <?= htmlspecialchars($currentElection['name'] ?? 'the election') ?></p>
                            </div>
                            <div class="d-flex align-items-center">
                                <button id="refreshResults" class="refresh-btn">
                                    <i class="bi bi-arrow-clockwise me-2"></i> Refresh
                                </button>
                                <span class="live-indicator"><i class="bi bi-broadcast me-1"></i>LIVE</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
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
                        <?php elseif ($currentElection): ?>
                            
                            <!-- Show vote success message -->
                            <?php if (isset($_GET['vote_success']) || isset($_SESSION['vote_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                                    <div>
                                        <strong>Success!</strong> Your vote has been successfully recorded. Thank you for participating in the election!
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="removeVoteSuccess()"></button>
                            </div>
                            <script>
                            function removeVoteSuccess() {
                                // Remove vote_success parameter from URL
                                if (window.history && window.history.replaceState) {
                                    var url = window.location.href;
                                    url = url.replace(/[&?]vote_success=1/, '');
                                    window.history.replaceState({}, document.title, url);
                                }
                                <?php unset($_SESSION['vote_success']); ?>
                            }
                            // Auto-hide after 10 seconds
                            setTimeout(function() {
                                const alertElement = document.querySelector('.alert-success');
                                if (alertElement) {
                                    const bsAlert = new bootstrap.Alert(alertElement);
                                    bsAlert.close();
                                    removeVoteSuccess();
                                }
                            }, 10000);
                            </script>
                            <?php endif; ?>
                            
                            <!-- Election Info -->
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
                                            <i class="bi bi-calendar-range me-2"></i>
                                            <?= date('F j, Y', strtotime($currentElection['startDate'])) ?> to <?= date('F j, Y', strtotime($currentElection['endDate'])) ?>
                                        </p>
                                        <div class="progress-wave mt-3"></div>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <div class="timer-countdown text-white mb-1" id="countdown-timer">
                                            <i class="bi bi-hourglass-split me-2"></i>
                                            <?= date('M j, Y', strtotime($currentElection['endDate'])) ?>
                                        </div>
                                        <p class="text-white-50 mb-0">
                                            <i class="bi bi-activity me-1"></i>
                                            Status: <span class="fw-bold"><?= $currentElection['status'] ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="stats-card">
                                        <div class="stats-icon blue">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                        <div class="stats-value"><?= number_format($totalVoters) ?></div>
                                        <div class="stats-label"><i class="bi bi-check-square me-1"></i>Total Votes Cast</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stats-card">
                                        <div class="stats-icon green">
                                            <i class="bi bi-person-check-fill"></i>
                                        </div>
                                        <div class="stats-value"><?= number_format($voterTurnout) ?>%</div>
                                        <div class="stats-label"><i class="bi bi-percent me-1"></i>Voter Turnout</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stats-card">
                                        <div class="stats-icon purple">
                                            <i class="bi bi-award-fill"></i>
                                        </div>
                                        <div class="stats-value"><?= count($positions) ?></div>
                                        <div class="stats-label"><i class="bi bi-bookmark-star me-1"></i>Positions</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Results by Position -->
                            <?php foreach ($positions as $position): ?>
                                <?php if (!empty($position['candidates'])): ?>
                                    <div class="position-section">
                                        <div class="position-header">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="position-badge text-white me-3">
                                                    <i class="bi bi-diagram-3-fill me-1"></i>Position
                                                </span>
                                                <h4 class="mb-0 fw-bold"><?= htmlspecialchars($position['title']) ?></h4>
                                            </div>
                                            <p class="text-muted small mb-0"><i class="bi bi-info-circle-fill me-1"></i><?= htmlspecialchars($position['description'] ?? '') ?></p>
                                            <p class="text-muted small mt-1">
                                                <i class="bi bi-clipboard-data me-1"></i>
                                                Total votes: <span class="fw-bold"><?= number_format($position['totalVotes']) ?></span>
                                            </p>
                                        </div>
                                        
                                        <div class="candidates-results">
                                            <?php 
                                            $maxVotes = 0;
                                            foreach ($position['candidates'] as $candidate) {
                                                if ((int)$candidate['voteCount'] > $maxVotes) {
                                                    $maxVotes = (int)$candidate['voteCount'];
                                                }
                                            }
                                            
                                            foreach ($position['candidates'] as $candidate): 
                                                $isLeading = (int)$candidate['voteCount'] === $maxVotes && $maxVotes > 0;
                                            ?>
                                                <div class="candidate-result <?= $isLeading ? 'leading' : '' ?>">
                                                    <div class="avatar-container">
                                                        <?php if ($isLeading): ?>
                                                            <div class="winner-badge" title="Leading Candidate">
                                                                <i class="bi bi-star-fill"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php 
                                                        // Check for candidate photo first, then fall back to profile picture
                                                        $candidatePhoto = !empty($candidate['photo']) ? 'uploads/candidates/' . $candidate['photo'] : '';
                                                        $profilePicPath = 'assets/img/profile/students/' . htmlspecialchars($candidate['profilePicture'] ?? '');
                                                        
                                                        if (!empty($candidate['photo']) && file_exists($candidatePhoto)): ?>
                                                            <img src="<?= $candidatePhoto ?>?t=<?= time() ?>" 
                                                                 class="avatar" 
                                                                 alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                        <?php elseif (!empty($candidate['profilePicture']) && file_exists($profilePicPath)): ?>
                                                            <img src="<?= $profilePicPath ?>?t=<?= time() ?>" 
                                                                 class="avatar" 
                                                                 alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                        <?php else: ?>
                                                            <div class="avatar bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary">
                                                                <i class="bi bi-person fs-2"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <span class="department-badge"><i class="bi bi-buildings-fill"></i><?= htmlspecialchars($candidate['department']) ?></span>
                                                    </div>
                                                    
                                                    <div class="candidate-info">
                                                        <h5 class="candidate-name">
                                                            <i class="bi bi-person-badge me-1"></i>
                                                            <?= htmlspecialchars($candidate['name']) ?>
                                                        </h5>
                                                        <p class="candidate-position">
                                                            <i class="bi bi-briefcase me-1"></i>
                                                            <?= htmlspecialchars($position['title']) ?>
                                                        </p>
                                                        <div class="d-flex align-items-center">
                                                            <div class="vote-count">
                                                                <i class="bi bi-check2-circle me-1"></i>
                                                                <?= number_format($candidate['voteCount']) ?>
                                                            </div>
                                                            <div class="vote-percentage">
                                                                <i class="bi bi-graph-up-arrow me-1"></i>
                                                                <?= isset($candidate['votePercentage']) ? $candidate['votePercentage'] : 0 ?>%
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar" 
                                                                 style="width: <?= isset($candidate['votePercentage']) ? $candidate['votePercentage'] : 0 ?>%;" 
                                                                 aria-valuenow="<?= isset($candidate['votePercentage']) ? $candidate['votePercentage'] : 0 ?>" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-4">
                                <a href="student.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left me-2"></i> Back to Voting Portal
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert bg-light border-0 rounded-4 p-4 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="counter-circle bg-secondary bg-opacity-10 text-secondary me-3">
                                        <i class="bi bi-calendar-x"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">No Election Selected</h5>
                                        <p class="mb-0 text-muted">Please select an election to view results.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main><br><br><br>

    <!-- Back to top button -->
    <div class="back-to-top" id="backToTop">
        <i class="bi bi-arrow-up"></i>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme handling script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get stored theme or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        
        // Apply theme on page load
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        
        // Listen for theme change events from header
        document.addEventListener('themeChanged', function(e) {
            document.documentElement.setAttribute('data-bs-theme', e.detail.theme);
        });
    });
    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh results every 30 seconds
        const refreshInterval = setInterval(refreshResults, 30000);
        
        // Manual refresh button
        document.getElementById('refreshResults').addEventListener('click', function() {
            this.disabled = true;
            const icon = this.querySelector('i');
            icon.classList.add('bi-arrow-clockwise-animate');
            
            setTimeout(() => {
                refreshResults();
            }, 500);
        });
        
        function refreshResults() {
            // Reload the page to get fresh data
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('cache', Date.now());
            window.location.href = currentUrl.toString();
        }
        
        // Clean up interval when page is closed
        window.addEventListener('beforeunload', function() {
            clearInterval(refreshInterval);
        });
        
        // Back to top button functionality
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Add animation to refresh button icon
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .bi-arrow-clockwise-animate {
                    animation: spin 1s linear infinite;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `);
    });
    </script>

    <!-- Include auto-refresh functionality -->
    <script>
        // Auto-refresh functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get current election ID from URL or data attribute
            const urlParams = new URLSearchParams(window.location.search);
            const electionID = urlParams.get('election') || <?= $electionID ?>;
            
            // Set refresh interval (30 seconds)
            const refreshInterval = 30000;
            
            // Function to refresh the page
            function refreshResults() {
                // If we're not actively interacting with the page, reload it
                if (!document.activeElement || !['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(document.activeElement.tagName)) {
                    window.location.reload();
                } else {
                    // If user is interacting with form elements, wait until they're done
                    console.log('User is interacting with the page. Delaying refresh.');
                    setTimeout(refreshResults, refreshInterval);
                }
            }
            
            // Set up the auto-refresh timer
            let refreshTimer = setTimeout(refreshResults, refreshInterval);
            
            // Reset the timer when user interacts with the page
            document.addEventListener('click', function() {
                clearTimeout(refreshTimer);
                refreshTimer = setTimeout(refreshResults, refreshInterval);
            });
            
            // Show refresh indicator
            const refreshIndicator = document.createElement('div');
            refreshIndicator.className = 'refresh-indicator';
            refreshIndicator.innerHTML = '<i class="bi bi-arrow-repeat"></i> <span id="refresh-countdown">30</span>';
            refreshIndicator.style.position = 'fixed';
            refreshIndicator.style.bottom = '20px';
            refreshIndicator.style.right = '20px';
            refreshIndicator.style.backgroundColor = 'rgba(67, 97, 238, 0.9)';
            refreshIndicator.style.color = 'white';
            refreshIndicator.style.padding = '8px 15px';
            refreshIndicator.style.borderRadius = '20px';
            refreshIndicator.style.fontSize = '12px';
            refreshIndicator.style.fontWeight = 'bold';
            refreshIndicator.style.zIndex = '9999';
            refreshIndicator.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
            document.body.appendChild(refreshIndicator);
            
            // Update countdown
            const countdownElement = document.getElementById('refresh-countdown');
            let secondsLeft = 30;
            
            setInterval(function() {
                secondsLeft -= 1;
                if (secondsLeft <= 0) {
                    secondsLeft = 30;
                }
                countdownElement.textContent = secondsLeft;
            }, 1000);
            
            // Add manual refresh button
            refreshIndicator.addEventListener('click', function() {
                window.location.reload();
            });
            refreshIndicator.style.cursor = 'pointer';
            refreshIndicator.title = 'Click to refresh now';
            
            // Add AJAX-based results update (optional, only for browsers that support fetch)
            if (window.fetch) {
                // Add capability to update results without full page refresh
                setInterval(function() {
                    fetch('calculate_vote_results.php?ajax=1&election=' + electionID)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Results updated via AJAX');
                            }
                        })
                        .catch(error => console.error('Error updating results:', error));
                }, refreshInterval * 2); // Run AJAX update less frequently than page refresh
            }
        });
    </script>
</body>
</html>