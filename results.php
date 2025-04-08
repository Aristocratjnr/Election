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
            background-color: #f8f9fa;
        }
        .main-content {
            margin-left: 10px;
           
        }
        
        /* Card Styles */
        .card {
            border-radius: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }
        
        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem 1.25rem;
        }
        
        /* Icon Styles */
        .card-icon {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
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
        
        /* Progress Bar Styles */
        .progress-thin {
            height: 6px;
            border-radius: 3px;
        }
        .progress-bar-custom {
            background-color: #e9ecef;
            border-radius: 0.5rem;
            height: 0.75rem;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .progress-custom {
            background: linear-gradient(to right, #4e73df, #224abe);
            border-radius: 0.5rem;
            height: 100%;
            transition: width 1s ease-in-out;
        }
        
        /* Avatar and Image Styles */
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        .initials-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            font-weight: bold;
            color: #6c757d;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        .candidate-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover .candidate-photo {
            transform: scale(1.05);
        }
        
        /* Badge Styles */
        .winner-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(45deg, #ffc107, #ff9800);
            color: white;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
            z-index: 2;
            border: 2px solid white;
            transition: transform 0.2s;
        }
        
        /* Text Styles */
        .position-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }
        
        
        .btn-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
            border: none;
        }
        .btn-outline-primary {
            border-color: #4e73df;
            color: #4e73df;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(to right, #4e73df, #224abe);
        }
        
        /* Filter Section */
        .filter-section {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 0.8rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        
        /* Results Cards */
        .results-card {
            border: none;
            border-radius: 0.8rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }
        
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 0;
        }
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #6c757d;
            opacity: 0.5;
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
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        .section-header i {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?>
                <br>
                <div class="card w-75 mx-auto shadow-sm border-0">
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4"><br>
                    <!-- Page Header with Breadcrumb -->
                    <nav aria-label="breadcrumb" class="no-print mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-bar-chart-fill"></i> Election Results</li>
                        </ol>
                    </nav>
                    
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                        <h1 class="h2"><i class="bi bi-trophy-fill text-warning me-2"></i> Election Results</h1>
                        <div class="btn-toolbar mb-2 mb-md-0 no-print">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="bi bi-printer-fill"></i> Print
                                </button>
                                <a href="export_results.php?election=<?php echo $electionID; ?>&type=excel" 
                                   class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                                </a>
                                <a href="export_results.php?election=<?php echo $electionID; ?>&type=pdf" 
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                </a>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-share-fill"></i> Share
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-envelope-fill"></i> Email</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-link-45deg"></i> Copy Link</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-broadcast"></i> Publish</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Election Filter -->
                    <div class="card border-0 shadow-sm mb-4 filter-section no-print">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0"><i class="bi bi-funnel-fill text-primary me-2"></i> Election Filter</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center">
                                        <i class="bi bi-calendar2-event text-primary me-2"></i>
                                        Select Election
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <select class="form-select" name="election" onchange="this.form.submit()">
                                            <option value="">-- Select Election --</option>
                                            <?php while ($election = $elections->fetch_assoc()): ?>
                                            <option value="<?php echo $election['electionID']; ?>" 
                                                <?php echo $electionID == $election['electionID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($election['name']); ?>
                                                (<?php echo date('M Y', strtotime($election['startDate'])); ?>)
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-filter-square-fill"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <?php if ($electionID): ?>
                                <div class="col-md-6">
                                    <label class="form-label d-flex align-items-center">
                                        <i class="bi bi-clock-history text-primary me-2"></i>
                                        Results Generated
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('F j, Y, g:i a'); ?>" readonly>
                                        <span class="input-group-text"><i class="bi bi-check2-circle text-success"></i></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <!-- Election Header Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="mb-2 d-flex align-items-center">
                                        <i class="bi bi-award-fill text-warning me-2"></i>
                                        <?php echo htmlspecialchars($electionDetails['name']); ?>
                                    </h3>
                                    <p class="text-muted mb-0 d-flex align-items-center">
                                        <i class="bi bi-calendar-range me-2"></i>
                                        <?php echo date('F j, Y', strtotime($electionDetails['startDate'])); ?> - 
                                        <?php echo date('F j, Y', strtotime($electionDetails['endDate'])); ?>
                                        <span class="badge bg-<?php echo $electionDetails['status'] == 'active' ? 'success' : ($electionDetails['status'] == 'completed' ? 'primary' : 'warning'); ?> ms-3">
                                            <i class="bi bi-<?php echo $electionDetails['status'] == 'active' ? 'check-circle-fill' : ($electionDetails['status'] == 'completed' ? 'flag-fill' : 'clock-fill'); ?> me-1"></i>
                                            <?php echo ucfirst($electionDetails['status']); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="d-inline-block border rounded-pill px-3 py-2 text-primary fw-bold">
                                        <i class="bi bi-people-fill me-2"></i>
                                        Total Votes: <?php echo number_format($totalVotes); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    

                    <!-- Heading for Results Section -->
                    <div class="mt-5 mb-4 section-header">
                        <i class="bi bi-clipboard-data fs-3 text-primary"></i>
                        <h2>Results by Position</h2>
                    </div>

                    <!-- Results by Position -->
                    <?php if (!empty($resultsData)): ?>
                        <?php foreach ($resultsData as $position): ?>
                        <div class="card border-0 shadow-sm mb-4 results-card">
                            <div class="card-header bg-white py-3">
                                <h3 class="position-title mb-0">
                                    <i class="bi bi-person-badge fs-4 text-primary"></i>
                                    <?php echo htmlspecialchars($position['title']); ?>
                                    <span class="badge bg-secondary ms-2">
                                        <i class="bi bi-check2-all me-1"></i>
                                        Max Votes: <?php echo $position['maxVotes']; ?>
                                    </span>
                                    <span class="badge bg-info ms-2">
                                        <i class="bi bi-people-fill me-1"></i>
                                        Total: <?php echo $position['totalVotes']; ?> votes
                                    </span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <?php 
                                    $maxVotes = !empty($position['candidates']) ? max(array_column($position['candidates'], 'voteCount')) : 0;
                                    foreach ($position['candidates'] as $candidate): 
                                        $isWinner = ($candidate['voteCount'] == $maxVotes && $maxVotes > 0);
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm h-100 position-relative">
                                            <?php if ($isWinner): ?>
                                            <span class="winner-badge" title="Winner">
                                                <i class="bi bi-trophy-fill"></i>
                                            </span>
                                            <?php endif; ?>
                                            <div class="card-body text-center candidate-details">
                                                <?php if ($candidate['profilePicture']): ?>
                                                <img src="assets/img/profile/students/<?php echo $candidate['profilePicture']; ?>" 
                                                     class="candidate-photo mb-3" 
                                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                     onerror="this.onerror=null;this.src='assets/img/default-profile.png'">
                                                <?php else: ?>
                                                <div class="candidate-photo bg-light d-flex align-items-center justify-content-center mx-auto mb-3">
                                                    <i class="bi bi-person text-muted" style="font-size: 2rem;"></i>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <h5 class="mb-2">
                                                    <i class="bi bi-person-circle me-1 text-primary"></i>
                                                    <?php echo htmlspecialchars($candidate['name']); ?>
                                                </h5>
                                                
                                                <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted d-flex align-items-center">
                                                        <i class="bi bi-check2-square me-1"></i>
                                                        Votes
                                                    </span>
                                                    <span class="vote-count d-flex align-items-center">
                                                        <i class="bi bi-123 me-1"></i>
                                                        <?php echo number_format($candidate['voteCount']); ?>
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
                                                    <span class="percentage d-flex align-items-center">
                                                        <i class="bi bi-graph-up-arrow me-1"></i>
                                                        <?php echo $candidate['percentage']; ?>%
                                                    </span>
                                                </div>
                                                
                                                <?php if ($isWinner): ?>
                                                <div class="mt-3">
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-trophy-fill me-1"></i>
                                                        Winner
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Print/Export Buttons -->
                        <div class="text-center mt-4 no-print">
                            <button class="btn btn-primary me-3 px-4" onclick="window.print()">
                                <i class="bi bi-printer-fill me-2"></i> Print Results
                            </button>
                            <button class="btn btn-success me-3 px-4" id="exportExcel">
                                <i class="bi bi-file-earmark-excel-fill me-2"></i> Export to Excel
                            </button>
                            <button class="btn btn-danger px-4" id="exportPDF">
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i> Save as PDF
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- No Results Message -->
                        <div class="card border-0 shadow-sm text-center py-5 empty-state">
                            <div class="card-body">
                                <i class="bi bi-info-circle-fill empty-state-icon"></i>
                                <h4 class="mt-3 mb-2">No results available</h4>
                                <p class="text-muted mb-4">Results will be displayed once voting has concluded and tallied.</p>
                                <a href="elections.php" class="btn btn-primary">
                                    <i class="bi bi-calendar2-event me-2"></i> View Elections
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php elseif ($electionID): ?>
                    <!-- Election Not Found -->
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                            <h4 class="mt-3 mb-2">Election Not Found</h4>
                            <p class="text-muted mb-4">The election you selected doesn't exist or may have been removed.</p>
                            <a href="results.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2"></i> Back to Results
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Select Election Message -->
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-info-circle-fill text-primary" style="font-size: 3rem;"></i>
                            <h4 class="mt-3 mb-2">Select an Election</h4>
                            <p class="text-muted mb-4">Choose an election from the dropdown to view detailed voting results.</p>
                            <button class="btn btn-primary" onclick="document.querySelector('select[name=\'election\']').focus()">
                                <i class="bi bi-arrow-up-circle me-2"></i> Select Election
                            </button>
                        </div>
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
        // Animate progress bars
        const progressBars = document.querySelectorAll('.progress-custom');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
        
        // Export to Excel
        document.getElementById('exportExcel').addEventListener('click', function() {
            // Create a workbook
            const wb = XLSX.utils.book_new();
            
            // Get all the results data
            const results = <?php echo json_encode($resultsData); ?>;
            
            // Prepare data for export
            const exportData = [];
            
            results.forEach(position => {
                position.candidates.forEach(candidate => {
                    exportData.push({
                        'Position': position.title,
                        'Candidate Name': candidate.name,
                        'Votes': candidate.voteCount,
                        'Percentage': candidate.percentage + '%',
                        'Is Winner': candidate.voteCount === Math.max(...position.candidates.map(c => c.voteCount)) ? 'Yes' : 'No'
                    });
                });
            });
            
            // Create a worksheet
            const ws = XLSX.utils.json_to_sheet(exportData);
            
            // Add the worksheet to the workbook
            XLSX.utils.book_append_sheet(wb, ws, "Election Results");
            
            // Export the workbook
            XLSX.writeFile(wb, 'Election_Results_<?php echo isset($electionDetails['name']) ? preg_replace('/[^a-zA-Z0-9]/', '_', $electionDetails['name']) : 'Results'; ?>_<?php echo date('Y-m-d'); ?>.xlsx');
        });
        
        // Export to PDF
        document.getElementById('exportPDF').addEventListener('click', function() {
            // Create a new PDF document
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(18);
            doc.setTextColor(40);
            doc.text('Election Results: <?php echo isset($electionDetails['name']) ? $electionDetails['name'] : ''; ?>', 105, 15, { align: 'center' });
            
            // Add date
            doc.setFontSize(12);
            doc.setTextColor(100);
            doc.text('Generated on: <?php echo date('F j, Y, g:i a'); ?>', 105, 22, { align: 'center' });
            
            // Add line
            doc.setDrawColor(200);
            doc.setLineWidth(0.5);
            doc.line(20, 25, 190, 25);
            
            let yPosition = 35;
            
            // Add results for each position
            const results = <?php echo json_encode($resultsData); ?>;
            
            results.forEach((position, index) => {
                // Add position title
                if (index > 0) {
                    doc.addPage();
                    yPosition = 20;
                }
                
                doc.setFontSize(14);
                doc.setTextColor(40);
                doc.text(position.title + ' (Max Votes: ' + position.maxVotes + ')', 20, yPosition);
                yPosition += 10;
                
                // Add candidates
                position.candidates.forEach(candidate => {
                    if (yPosition > 250) {
                        doc.addPage();
                        yPosition = 20;
                    }
                    
                    // Candidate name
                    doc.setFontSize(12);
                    doc.setTextColor(60);
                    doc.text(candidate.name, 25, yPosition);
                    
                    // Votes and percentage
                    doc.text('Votes: ' + candidate.voteCount + ' (' + candidate.percentage + '%)', 25, yPosition + 7);
                    
                    // Progress bar
                    doc.setDrawColor(200);
                    doc.setLineWidth(0.5);
                    doc.line(25, yPosition + 12, 185, yPosition + 12);
                    
                    doc.setFillColor(78, 115, 223);
                    doc.rect(25, yPosition + 12, (160 * candidate.percentage / 100), 3, 'F');
                    
                    yPosition += 20;
                });
                
                yPosition += 10;
            });
            
            // Save the PDF
            doc.save('Election_Results_<?php echo isset($electionDetails['name']) ? preg_replace('/[^a-zA-Z0-9]/', '_', $electionDetails['name']) : 'Results'; ?>_<?php echo date('Y-m-d'); ?>.pdf');
        });
        
        // Share functionality
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.querySelector('i').className.split(' ')[1];
                
                switch(action) {
                    case 'bi-envelope-fill':
                        alert('Email sharing would be implemented here');
                        break;
                    case 'bi-link-45deg':
                        navigator.clipboard.writeText(window.location.href)
                            .then(() => alert('Link copied to clipboard!'))
                            .catch(() => alert('Failed to copy link'));
                        break;
                    case 'bi-broadcast':
                        alert('Publishing results would be implemented here');
                        break;
                }
            });
        });
    });
    </script>
  
</body>
</html>