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
    // Fetch election details
    $stmt = $conn->prepare("
        SELECT * FROM elections 
        WHERE electionID = ?
    ");
    $stmt->bind_param('i', $electionID);
    $stmt->execute();
    $currentElection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$currentElection) {
        $error = "Election not found.";
    } else {
        // Get positions for the election
        $stmt = $conn->prepare("SELECT * FROM positions WHERE electionID = ?");
        $stmt->bind_param('i', $electionID);
        $stmt->execute();
        $positions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get candidates and vote counts for each position
        foreach ($positions as &$position) {
            $stmt = $conn->prepare("
                SELECT c.*, s.name, s.department, s.profilePicture, 
                       COUNT(v.voteID) as voteCount
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                LEFT JOIN votes v ON c.candidateID = v.candidateID AND v.electionID = ?
                WHERE c.positionID = ? AND c.status = 'Approved'
                GROUP BY c.candidateID
                ORDER BY voteCount DESC, s.name ASC
            ");
            $stmt->bind_param('ii', $electionID, $position['positionID']);
            $stmt->execute();
            $position['candidates'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calculate total votes for this position
            $totalVotes = 0;
            foreach ($position['candidates'] as $candidate) {
                $totalVotes += (int)$candidate['voteCount'];
            }
            $position['totalVotes'] = $totalVotes;

            // Calculate vote percentages
            foreach ($position['candidates'] as &$candidate) {
                $candidate['votePercentage'] = $totalVotes > 0 ? 
                    round(($candidate['voteCount'] / $totalVotes) * 100, 1) : 0;
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
            padding: 1rem;
            border-radius: 12px;
            background-color: white;
            border: 1px solid var(--border);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .candidate-result:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(67, 97, 238, 0.3);
        }
        
        .candidate-result.leading {
            border-left: 4px solid var(--success);
        }
        
        .avatar-container {
            width: 60px;
            height: 60px;
            margin-right: 1rem;
            position: relative;
        }
        
        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border);
            transition: all 0.4s ease;
        }
        
        .department-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--surface);
            border: 1px solid var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            letter-spacing: 0.5px;
            text-transform: uppercase;
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
            background: white;
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
                                <h2 class="mb-1 fw-bold"><i class="bi bi-graph-up-arrow role-icon icon"></i>&nbsp;Live Results</h2>
                                <p class="text-muted mb-0">Real-time voting results for <?= htmlspecialchars($currentElection['name'] ?? 'the election') ?></p>
                            </div>
                            <div class="d-flex align-items-center">
                                <button id="refreshResults" class="refresh-btn">
                                    <i class="bi bi-arrow-clockwise me-2"></i> Refresh
                                </button>
                                <span class="live-indicator">LIVE</span>
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
                            
                            <!-- Stats Cards -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="stats-card">
                                        <div class="stats-icon blue">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                        <div class="stats-value"><?= number_format($totalVoters) ?></div>
                                        <div class="stats-label">Total Votes Cast</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stats-card">
                                        <div class="stats-icon green">
                                            <i class="bi bi-person-check-fill"></i>
                                        </div>
                                        <div class="stats-value"><?= number_format($voterTurnout) ?>%</div>
                                        <div class="stats-label">Voter Turnout</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stats-card">
                                        <div class="stats-icon purple">
                                            <i class="bi bi-trophy-fill"></i>
                                        </div>
                                        <div class="stats-value"><?= count($positions) ?></div>
                                        <div class="stats-label">Positions</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Results by Position -->
                            <?php foreach ($positions as $position): ?>
                                <?php if (!empty($position['candidates'])): ?>
                                    <div class="position-section">
                                        <div class="position-header">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="position-badge text-white me-3">Position</span>
                                                <h4 class="mb-0 fw-bold"><?= htmlspecialchars($position['title']) ?></h4>
                                            </div>
                                            <p class="text-muted small mb-0"><?= htmlspecialchars($position['description'] ?? '') ?></p>
                                            <p class="text-muted small mt-1">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Total votes: <?= number_format($position['totalVotes']) ?>
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
                                                    
                                                    <div class="candidate-info">
                                                        <h5 class="candidate-name"><?= htmlspecialchars($candidate['name']) ?></h5>
                                                        <p class="candidate-position"><?= htmlspecialchars($position['title']) ?></p>
                                                        
                                                        <div class="d-flex align-items-center">
                                                            <div class="vote-count"><?= number_format($candidate['voteCount']) ?></div>
                                                            <div class="vote-percentage"><?= $candidate['votePercentage'] ?>%</div>
                                                        </div>
                                                        
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar" 
                                                                 style="width: <?= $candidate['votePercentage'] ?>%;" 
                                                                 aria-valuenow="<?= $candidate['votePercentage'] ?>" 
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

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh results every 30 seconds
        const refreshInterval = setInterval(refreshResults, 30000);
        
        // Manual refresh button
        document.getElementById('refreshResults').addEventListener('click', function() {
            refreshResults();
        });
        
        function refreshResults() {
            // Reload the page to get fresh data
            window.location.reload();
        }
        
        // Clean up interval when page is closed
        window.addEventListener('beforeunload', function() {
            clearInterval(refreshInterval);
        });
    });
    </script>
</body>
</html> 