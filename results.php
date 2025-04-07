<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Initialize variables
$electionID = $_GET['election'] ?? null;
$electionDetails = null;
$resultsData = [];
$totalVotes = 0;

if ($electionID) {
    // Get election details
    $electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionDetails = $electionStmt->get_result()->fetch_assoc();

    // Get all positions for this election
    $positions = $conn->query("
        SELECT * FROM positions 
        WHERE electionID = $electionID
        ORDER BY positionID ASC
    ");

    // Get results grouped by position
    if ($positions->num_rows > 0) {
        while ($position = $positions->fetch_assoc()) {
            $positionID = $position['positionID'];
            
            // Get candidates and their results for this position
            $candidates = $conn->query("
                SELECT c.candidateID, c.studentID, c.position, s.name, s.profilePicture,
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
                $positionResults['candidates'][] = $candidate;
                $positionResults['totalVotes'] += $candidate['voteCount'];
                $totalVotes += $candidate['voteCount'];
            }

            // Calculate percentages if not stored
            foreach ($positionResults['candidates'] as &$candidate) {
                if ($positionResults['totalVotes'] > 0) {
                    $candidate['percentage'] = number_format(($candidate['voteCount'] / $positionResults['totalVotes']) * 100, 2);
                }
            }

            $resultsData[] = $positionResults;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary-color: #f8f9fc;
            --accent-color: #4cc9f0;
            --success-color: #4dd4ac;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --gray-dark: #212529;
            --gray-medium: #6c757d;
            --gray-light: #e9ecef;
        }
        
        body {
            background-color: #f0f2f5;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(145deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            position: fixed;
            height: 100vh;
            z-index: 100;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            transition: all 0.3s ease;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .results-card {
            border-left: 4px solid var(--primary-color);
            margin-bottom: 2rem;
        }
        
        .candidate-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
    
        .progress {
            height: 0.85rem;
            border-radius: 1rem;
            background-color: var(--gray-light);
            overflow: hidden;
        }
        
        .progress-bar {
            background-image: linear-gradient(to right, var(--primary-light), var(--primary-color));
            border-radius: 1rem;
            transition: width 1.2s cubic-bezier(0.65, 0, 0.35, 1);
        }
        
        .stat-card {
            border-radius: 1rem;
            color: white;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
            background-image: linear-gradient(145deg, rgba(255, 255, 255, 0.15) 0%, rgba(0, 0, 0, 0.1) 100%);
        }
    
        
        .bg-gradient-primary {
            background: linear-gradient(45deg, var(--primary-dark) 0%, var(--primary-color) 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(45deg, #2bb673 0%, var(--success-color) 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(45deg, #3a86ff 0%, var(--accent-color) 100%);
        }
        
        .stat-card i {
            font-size: 2.75rem;
            opacity: 0.85;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .card-title {
            font-size: 1rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin-bottom: 0.75rem;
        }
        
        .stat-card .card-text {
            font-size: 1.75rem;
            font-weight: 700;
        }
        
        .candidate-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
            border-radius: 1rem;
            overflow: hidden;
        }
        
        
        .winner-badge {
            position: absolute;
            top: -12px;
            right: -12px;
            background: linear-gradient(45deg, #ffc107 0%, #ff9800 100%);
            color: white;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
            z-index: 1;
            border: 3px solid white;
            transition: transform 0.3s ease;
        }
        
        
        .position-title {
            color: var(--gray-dark);
            font-weight: 700;
            margin-bottom: 1.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(67, 97, 238, 0.2);
            position: relative;
        }
        
        .position-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .vote-count {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }
        
        .percentage {
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .filter-card {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .filter-card .form-select {
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1rem;
            font-size: 1rem;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .filter-card .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
            border-color: var(--primary-color);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-header i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 0.25rem 0.5rem rgba(67, 97, 238, 0.2);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(67, 97, 238, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(67, 97, 238, 0.2);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-buttons .btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert {
            border-radius: 0.75rem;
            border: none;
            padding: 1.5rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08);
        }
        
        .alert-info {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--accent-color);
        }
        
        .alert-warning {
            background-color: rgba(255, 209, 102, 0.1);
            color: var(--warning-color);
        }
        
        .candidate-info {
            display: flex;
            flex-direction: column;
            padding: 1rem;
        }
        
        .candidate-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-dark);
        }
        
        .vote-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem 0;
        }
        
        .no-results i {
            font-size: 4rem;
            color: var(--gray-medium);
            margin-bottom: 1rem;
        }
        
        .page-header {
            background-color: white;
            padding: 1.5rem 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-dark);
            margin-bottom: 0;
        }
        
        .position-badge {
            display: inline-block;
            background-color: var(--primary-light);
            color: white;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            margin-left: 0.75rem;
            vertical-align: middle;
        }
        
        @media (max-width: 991px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
            
            .position-title {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .candidate-photo {
                width: 70px;
                height: 70px;
            }
        }
        
        /* Animation effects */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .scale-in {
            animation: scaleIn 0.3s ease-in-out;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .election-status {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background-color: rgba(77, 212, 172, 0.15);
            color: var(--success-color);
        }
        
        .status-completed {
            background-color: rgba(76, 201, 240, 0.15);
            color: var(--accent-color);
        }
        
        .status-upcoming {
            background-color: rgba(255, 209, 102, 0.15);
            color: var(--warning-color);
        }
        
        .winner-indicator {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background-color: var(--success-color);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?><br><br>
                
                <main class="col-12 px-md-4 py-5">
                    <div class="page-header animate__animated animate__fadeIn">
                        <div>
                            <h1 class="page-title">Election Results</h1>
                            <p class="text-muted mb-0">View and analyze election results by position and candidate</p>
                        </div>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                            <button type="button" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-excel"></i> Export
                            </button>
                        </div>
                    </div>

                    <div class="card filter-card animate__animated animate__fadeIn">
                        <div class="card-body p-4">
                            <div class="section-header">
                                <i class="bi bi-funnel-fill"></i>
                                <h5 class="mb-0 fw-bold">Election Filter</h5>
                            </div>
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Select Election</label>
                                    <select class="form-select shadow-sm" name="election" onchange="this.form.submit()">
                                        <option value="">-- Select Election --</option>
                                        <?php while ($election = $elections->fetch_assoc()): ?>
                                        <option value="<?php echo $election['electionID']; ?>" 
                                            <?php echo $electionID == $election['electionID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($election['name']); ?>
                                            (<?php echo date('M Y', strtotime($election['startDate'])); ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <?php if ($electionID): ?>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Results Generated</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-clock-history"></i></span>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('F j, Y, g:i a'); ?>" readonly>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <div class="card scale-in">
                        <div class="card-body p-4">
                            <div class="section-header">
                                <i class="bi bi-bar-chart-fill"></i>
                                <h5 class="mb-0 fw-bold">Election Overview</h5>
                            </div>
                            
                            <div class="row mb-4 mt-3">
                                <div class="col-md-4">
                                    <div class="stat-card bg-gradient-primary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title">Total Votes Cast</h6>
                                                <h2 class="card-text"><?php echo number_format($totalVotes); ?></h2>
                                            </div>
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card bg-gradient-success">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title">Election Status</h6>
                                                <div class="d-flex align-items-center">
                                                    <h2 class="card-text text-capitalize mb-0"><?php echo $electionDetails['status']; ?></h2>
                                                    <?php 
                                                    $statusClass = 'status-upcoming';
                                                    if ($electionDetails['status'] == 'active') $statusClass = 'status-active';
                                                    if ($electionDetails['status'] == 'completed') $statusClass = 'status-completed';
                                                    ?>
                                                </div>
                                            </div>
                                            <i class="bi bi-check2-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card bg-gradient-info">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title">Voting Period</h6>
                                                <p class="card-text mb-0">
                                                    <?php echo date('M j, Y', strtotime($electionDetails['startDate'])); ?> - 
                                                    <?php echo date('M j, Y', strtotime($electionDetails['endDate'])); ?>
                                                </p>
                                            </div>
                                            <i class="bi bi-calendar-event-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($resultsData)): ?>
                                <div class="position-results">
                                    <?php foreach ($resultsData as $index => $position): ?>
                                    <div class="results-card card mb-5 fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                        <div class="card-body p-4">
                                            <h3 class="position-title d-flex align-items-center">
                                                <?php echo htmlspecialchars($position['title']); ?>
                                                <span class="position-badge">Max Votes: <?php echo $position['maxVotes']; ?></span>
                                            </h3>
                                            
                                            <div class="row g-4">
                                                <?php 
                                                $maxVotes = max(array_column($position['candidates'], 'voteCount'));
                                                foreach ($position['candidates'] as $index => $candidate): 
                                                    $isWinner = ($candidate['voteCount'] == $maxVotes && $maxVotes > 0);
                                                ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="candidate-card card h-100 position-relative scale-in" style="animation-delay: <?php echo $index * 0.05 + 0.2; ?>s">
                                                        <?php if ($isWinner): ?>
                                                        <span class="winner-badge" title="Winner">
                                                            <i class="bi bi-trophy-fill"></i>
                                                        </span>
                                                        <?php endif; ?>
                                                        <div class="card-body p-4">
                                                            <div class="text-center mb-3">
                                                                <?php if ($candidate['profilePicture']): ?>
                                                                <img src="assets/img/profile/students/<?php echo $candidate['profilePicture']; ?>" 
                                                                     class="candidate-photo mb-3" 
                                                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                                                <?php else: ?>
                                                                <div class="candidate-photo bg-light d-flex align-items-center justify-content-center mx-auto mb-3">
                                                                    <i class="bi bi-person-fill text-muted" style="font-size: 2.5rem;"></i>
                                                                </div>
                                                                <?php endif; ?>
                                                                <h5 class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                                            </div>
                                                            
                                                            <div class="candidate-stats">
                                                                <div class="vote-stats">
                                                                    <span class="text-muted fw-medium">Total Votes</span>
                                                                    <span class="vote-count"><?php echo number_format($candidate['voteCount']); ?></span>
                                                                </div>
                                                                <div class="progress mb-3">
                                                                    <div class="progress-bar" 
                                                                         role="progressbar" 
                                                                         style="width: <?php echo $candidate['percentage']; ?>%" 
                                                                         aria-valuenow="<?php echo $candidate['percentage']; ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <small class="text-muted fw-medium">Vote Share</small>
                                                                    <strong class="percentage"><?php echo $candidate['percentage']; ?>%</strong>
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
                                </div>
                                
                                <div class="text-center mt-4 mb-5 fade-in" style="animation-delay: 0.5s;">
                                    <div class="d-flex justify-content-center gap-3">
                                        <button class="btn btn-primary" onclick="window.print()">
                                            <i class="bi bi-printer me-2"></i> Print Results
                                        </button>
                                        <button class="btn btn-outline-primary">
                                            <i class="bi bi-file-earmark-pdf me-2"></i> Save as PDF
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-results scale-in">
                                    <i class="bi bi-info-circle"></i>
                                    <h4 class="mt-3 mb-2">No results available for this election yet</h4>
                                    <p class="text-muted">Results will be displayed once voting has concluded and tallied.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php elseif ($electionID): ?>
                    <div class="alert alert-warning text-center py-5 animate__animated animate__fadeIn">
                        <i class="bi bi-exclamation-triangle-fill fs-1 mb-3"></i>
                        <h4 class="mt-2">Election Not Found</h4>
                        <p class="mb-0">The election you selected doesn't exist or may have been removed. Please select a different election.</p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center py-5 animate__animated animate__fadeIn">
                        <i class="bi bi-info-circle-fill fs-1 mb-3"></i>
                        <h4 class="mt-2">Select an Election</h4>
                        <p class="mb-3">Choose an election from the dropdown above to view detailed voting results and statistics.</p>
                                        <button class="btn btn-outline-primary" onclick="document.querySelector('select[name=\'election\']').focus()">
                                            <i class="bi bi-arrow-up-circle me-2"></i> Select Election
                                        </button>
                                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Animation for progress bars
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
            
            // Add animation to cards
            const cards = document.querySelectorAll('.animate__animated');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Smooth scroll for page elements
            if (window.location.hash) {
                setTimeout(() => {
                    const element = document.querySelector(window.location.hash);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 300);
            }
        });
        
        // Function to generate PDF
        function generatePDF() {
            // This would be replaced with actual PDF generation logic
            alert('PDF generation would be implemented here');
        }
        
        // Function to export data
        function exportData() {
            // This would be replaced with actual export logic
            alert('Data export would be implemented here');
        }
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>