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
            --primary-color: #4e73df;
            --primary-dark: #224abe;
            --secondary-color: #f8f9fc;
            --accent-color: #36b9cc;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            position: fixed;
            height: 100vh;
            z-index: 100;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(58, 59, 69, 0.2);
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
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .progress {
            height: 1rem;
            border-radius: 0.35rem;
            background-color: #eaecf4;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
            border-radius: 0.35rem;
            transition: width 1s ease-in-out;
        }
        
        .stat-card {
            border-radius: 0.5rem;
            color: white;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .stat-card .card-title {
            font-size: 1rem;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .card-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .candidate-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .candidate-card:hover {
            border-color: var(--primary-color);
        }
        
        .winner-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--success-color);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .position-title {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(78, 115, 223, 0.2);
        }
        
        .vote-count {
            font-weight: 700;
            color: var(--primary-dark);
        }
        
        .percentage {
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .select2-container--bootstrap5 .select2-selection {
            height: calc(2.5rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.35rem;
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?>
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                        <h1 class="h2 fw-bold text-primary">Election Results</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 animate__animated animate__fadeIn">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-funnel"></i> Filter Results</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Election</label>
                                    <select class="form-select form-select-lg" name="election" onchange="this.form.submit()">
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
                                    <label class="form-label">Results As Of</label>
                                    <input type="text" class="form-control form-control-lg" 
                                           value="<?php echo date('F j, Y, g:i a'); ?>" readonly>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <div class="card mb-4 animate__animated animate__fadeIn">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-bar-chart"></i> Election Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="stat-card bg-gradient-primary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title">Total Votes Cast</h6>
                                                <h2 class="card-text"><?php echo number_format($totalVotes); ?></h2>
                                            </div>
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card bg-gradient-success">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="card-title">Election Status</h6>
                                                <h2 class="card-text text-capitalize"><?php echo $electionDetails['status']; ?></h2>
                                            </div>
                                            <i class="bi bi-check-circle"></i>
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
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($resultsData)): ?>
                                <?php foreach ($resultsData as $position): ?>
                                <div class="results-card card mb-5 animate__animated animate__fadeInUp">
                                    <div class="card-body">
                                        <h3 class="position-title">
                                            <?php echo htmlspecialchars($position['title']); ?>
                                            <small class="text-muted fs-6">(Max Votes: <?php echo $position['maxVotes']; ?>)</small>
                                        </h3>
                                        
                                        <div class="row g-4">
                                            <?php 
                                            $maxVotes = max(array_column($position['candidates'], 'voteCount'));
                                            foreach ($position['candidates'] as $index => $candidate): 
                                                $isWinner = ($candidate['voteCount'] == $maxVotes && $maxVotes > 0);
                                            ?>
                                            <div class="col-md-6">
                                                <div class="candidate-card card h-100 position-relative">
                                                    <?php if ($isWinner): ?>
                                                    <span class="winner-badge" title="Winner">
                                                        <i class="bi bi-trophy"></i>
                                                    </span>
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-3 text-center">
                                                                <?php if ($candidate['profilePicture']): ?>
                                                                <img src="assets/img/profile/students/<?php echo $candidate['profilePicture']; ?>" 
                                                                     class="candidate-photo" 
                                                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                                                <?php else: ?>
                                                                <div class="candidate-photo bg-light d-flex align-items-center justify-content-center mx-auto">
                                                                    <i class="bi bi-person text-muted" style="font-size: 2rem;"></i>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-9">
                                                                <h5 class="mb-1"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-muted">Votes:</span>
                                                                    <strong class="vote-count"><?php echo number_format($candidate['voteCount']); ?></strong>
                                                                </div>
                                                                <div class="progress mb-2">
                                                                    <div class="progress-bar" 
                                                                         role="progressbar" 
                                                                         style="width: <?php echo $candidate['percentage']; ?>%" 
                                                                         aria-valuenow="<?php echo $candidate['percentage']; ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex justify-content-between">
                                                                    <small class="text-muted">Percentage</small>
                                                                    <strong class="percentage"><?php echo $candidate['percentage']; ?>%</strong>
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
                                
                                <div class="text-center mt-4">
                                    <button class="btn btn-primary" onclick="window.print()">
                                        <i class="bi bi-printer"></i> Print Results
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info text-center py-4">
                                    <i class="bi bi-info-circle fs-3"></i>
                                    <h4 class="mt-2">No results available for this election yet.</h4>
                                    <p class="mb-0">Please check back later when voting has concluded.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php elseif ($electionID): ?>
                    <div class="alert alert-warning text-center py-4 animate__animated animate__fadeIn">
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                        <h4 class="mt-2">Selected election not found</h4>
                        <p class="mb-0">The election you selected does not exist or has been removed.</p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center py-4 animate__animated animate__fadeIn">
                        <i class="bi bi-info-circle fs-3"></i>
                        <h4 class="mt-2">Select an election to view results</h4>
                        <p class="mb-0">Choose an election from the dropdown above to see detailed voting results.</p>
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
        });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>