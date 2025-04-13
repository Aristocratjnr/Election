<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

require 'configs/dbconnection.php';

// Check if user is admin or student
$isAdmin = ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');

// Get all elections
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Initialize variables
$electionID = $_GET['election'] ?? null;
$electionDetails = null;
$voteData = [];
$totalVotes = 0;
$uniqueVoters = 0;

if ($electionID) {
    // Get election details
    $electionStmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionDetails = $electionStmt->get_result()->fetch_assoc();

    // Modified query to get all candidates for this election with their vote counts
    $candidatesQuery = "
        SELECT c.candidateID, 
               st.name as candidateName, 
               st.profilePicture as candidatePhoto,
               p.title as position,
               c.manifesto
        FROM candidates c
        JOIN students st ON c.studentID = st.studentID
        JOIN positions p ON c.positionID = p.positionID
        WHERE p.electionID = ? AND c.status = 'Approved'
        ORDER BY p.display_order, p.positionID, st.name
    ";
    
    $candidatesStmt = $conn->prepare($candidatesQuery);
    $candidatesStmt->bind_param("i", $electionID);
    $candidatesStmt->execute();
    $candidatesResult = $candidatesStmt->get_result();
    
    // Initialize voteData with all candidates (including those with zero votes)
    while ($candidate = $candidatesResult->fetch_assoc()) {
        if (!isset($voteData[$candidate['candidateID']])) {
            $voteData[$candidate['candidateID']] = [
                'candidateID' => $candidate['candidateID'],
                'candidateName' => $candidate['candidateName'],
                'position' => $candidate['position'],
                'photo' => $candidate['candidatePhoto'],
                'votes' => [],
                'voteCount' => 0
            ];
        }
    }
    
    // Now get all votes for this election to add to the candidate data
    $votesQuery = "
        SELECT v.voteID, v.timestamp, 
               s.studentID, s.name as voterName, s.department as voterDepartment, s.profilePicture as voterPhoto,
               c.candidateID, p.title as position,
               st.name as candidateName, st.profilePicture as candidatePhoto
        FROM votes v
        JOIN students s ON v.studentID = s.studentID
        JOIN candidates c ON v.candidateID = c.candidateID
        JOIN students st ON c.studentID = st.studentID
        JOIN positions p ON c.positionID = p.positionID
        WHERE v.electionID = ?
        ORDER BY v.timestamp DESC
    ";
    
    $votesStmt = $conn->prepare($votesQuery);
    $votesStmt->bind_param("i", $electionID);
    $votesStmt->execute();
    $votesResult = $votesStmt->get_result();
    
    // Add votes to the candidates
    while ($vote = $votesResult->fetch_assoc()) {
        if (isset($voteData[$vote['candidateID']])) {
            $voteData[$vote['candidateID']]['votes'][] = $vote;
            $voteData[$vote['candidateID']]['voteCount']++;
        }
    }

    // Get unique voters count
    $uniqueQuery = $conn->prepare("SELECT COUNT(DISTINCT studentID) as count FROM votes WHERE electionID = ?");
    $uniqueQuery->bind_param("i", $electionID);
    $uniqueQuery->execute();
    $uniqueResult = $uniqueQuery->get_result();
    $uniqueVoters = $uniqueResult->fetch_assoc()['count'];

    // Get total votes count directly from database
    $totalVotesQuery = $conn->prepare("SELECT COUNT(*) as count FROM votes WHERE electionID = ?");
    $totalVotesQuery->bind_param("i", $electionID);
    $totalVotesQuery->execute();
    $totalVotesResult = $totalVotesQuery->get_result();
    $totalVotes = $totalVotesResult->fetch_assoc()['count'];
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vote Records - SmartVote</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --info: #0dcaf0;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background-color: #f5f7fa;
        }
        
        .main-content {
            width: 100%;
            min-height: 100vh;
            padding-left: 20px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding-left: 0;
            }
        }
        
        .card {
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        
        
        .card-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
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
        
        .progress-thin {
            height: 6px;
            border-radius: 10px;
            overflow: hidden;
            background-color: rgba(0,0,0,0.05);
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box:before {
            content: "\F52A";
            font-family: bootstrap-icons;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .search-box input {
            padding-left: 40px;
            border-radius: 50px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            border-color: #86b7fe;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .vote-item {
            border-left: 4px solid #0d6efd;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            background-color: white;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        }
        
     
        
        .vote-count-badge {
            background: linear-gradient(45deg, #0d6efd, #6610f2);
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
        }
        
        .candidate-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #0a58ca, #084298);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .btn-outline-secondary {
            border: 1px solid #dee2e6;
            color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #212529;
            border-color: #ced4da;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        
        /* Export Modal Styles */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
            padding: 1.25rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .list-group-item {
            border: 1px solid rgba(0,0,0,0.08);
            margin-bottom: 8px;
            border-radius: 10px !important;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
       
        
        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }
        
        .form-check-input:checked + .form-check-label {
            color: #198754;
            font-weight: 500;
        }
        
        .form-select, .form-control {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            font-size: 0.95rem;
        }
        
        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            border-color: #86b7fe;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .btn-success {
            background: linear-gradient(45deg, #198754, #20c997);
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
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
                
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4"><br>
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                        <div>
                            <h1 class="h2 mb-0">
                                <i class="bi bi-clipboard-check text-primary me-2"></i> Vote Records
                            </h1>
                            <nav aria-label="breadcrumb" class="mt-2">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Home</a></li>
                                    <li class="breadcrumb-item"><a href="elections.php" class="text-decoration-none"><i class="bi bi-award"></i> Elections</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-clipboard-check"></i> Vote Records</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <?php if ($isAdmin && $electionID): ?>
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center" id="printBtn">
                                    <i class="bi bi-printer me-1"></i> Print
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exportModal" id="exportBtn">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                                    <i class="bi bi-share me-1"></i> Share
                                </button>
                            </div>
                            <?php endif; ?>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="bi bi-funnel-fill me-1"></i> Filter
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><h6 class="dropdown-header"><i class="bi bi-calendar3"></i> Election Filter</h6></li>
                                    <li><a class="dropdown-item d-flex align-items-center" href="votes.php">
                                        <i class="bi bi-collection me-2 text-primary"></i> All Elections
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php 
                                    $elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");
                                    while ($election = $elections->fetch_assoc()): 
                                    ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center <?php echo $electionID == $election['electionID'] ? 'active' : ''; ?>" 
                                           href="votes.php?election=<?php echo $election['electionID']; ?>">
                                            <i class="bi bi-calendar-event me-2 <?php echo $electionID == $election['electionID'] ? 'text-white' : 'text-primary'; ?>"></i>
                                            <?php echo htmlspecialchars($election['name']); ?>
                                            <span class="ms-auto badge bg-<?php echo $election['status'] == 'Ongoing' ? 'success' : 'secondary'; ?> rounded-pill">
                                                <?php echo $election['status'] == 'Ongoing' ? 'Live' : 'Ended'; ?>
                                            </span>
                                        </a>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Modal -->
                    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="exportModalLabel">
                                        <i class="bi bi-cloud-arrow-down-fill me-2"></i>Export Vote Records
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="export_votes.php" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="election_id" value="<?php echo $electionID; ?>">
                                        
                                        <div class="mb-4">
                                            <label class="form-label d-flex align-items-center">
                                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                                Export Format
                                            </label>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatCSV" value="csv" checked>
                                                    <label class="form-check-label d-flex align-items-center" for="formatCSV">
                                                        <i class="bi bi-filetype-csv fs-5 me-2 text-success"></i> CSV
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel">
                                                    <label class="form-check-label d-flex align-items-center" for="formatExcel">
                                                        <i class="bi bi-file-earmark-excel fs-5 me-2 text-success"></i> Excel
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatPDF" value="pdf">
                                                    <label class="form-check-label d-flex align-items-center" for="formatPDF">
                                                        <i class="bi bi-file-earmark-pdf fs-5 me-2 text-danger"></i> PDF
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatJSON" value="json">
                                                    <label class="form-check-label d-flex align-items-center" for="formatJSON">
                                                        <i class="bi bi-filetype-json fs-5 me-2 text-warning"></i> JSON
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label d-flex align-items-center">
                                                <i class="bi bi-card-checklist me-2 text-primary"></i>
                                                Data to Export
                                            </label>
                                            <div class="list-group shadow-sm">
                                                <label class="list-group-item d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="checkbox" name="exportStats" checked>
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
                                                            <strong>Election Statistics</strong>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Vote counts, participation rates, and general metrics</small>
                                                    </div>
                                                </label>
                                                <label class="list-group-item d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="checkbox" name="exportVoters" checked>
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-people-fill me-2 text-success"></i>
                                                            <strong>Voters List</strong>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Complete list of voters with timestamps</small>
                                                    </div>
                                                </label>
                                                <label class="list-group-item d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="checkbox" name="exportBreakdown" checked>
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-pie-chart-fill me-2 text-info"></i>
                                                            <strong>Vote Breakdown</strong>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Detailed breakdown of votes by candidates and positions</small>
                                                    </div>
                                                </label>
                                                <label class="list-group-item d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="checkbox" name="exportTrends">
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-activity me-2 text-warning"></i>
                                                            <strong>Voting Trends</strong>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Time-based analysis of voting patterns</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary d-flex align-items-center" data-bs-dismiss="modal">
                                            <i class="bi bi-x-lg me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success d-flex align-items-center">
                                            <i class="bi bi-cloud-download-fill me-2"></i>Export Report
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Print Styles -->
                    <style media="print">
                        @page {
                            size: auto;
                            margin: 20mm;
                        }
                        body * {
                            visibility: hidden;
                        }
                        .print-section, .print-section * {
                            visibility: visible;
                        }
                        .print-section {
                            position: absolute;
                            left: 0;
                            top: 0;
                            width: 100%;
                        }
                        .no-print {
                            display: none !important;
                        }
                        .card {
                            border: none !important;
                            box-shadow: none !important;
                        }
                        .card-header {
                            background-color: #f8f9fa !important;
                            border-bottom: 1px solid #dee2e6 !important;
                        }
                        .vote-item {
                            border-left: 3px solid #0d6efd !important;
                            page-break-inside: avoid;
                        }
                    </style>
                    
                    <!-- Filter Card -->
                    <div class="card border-0 shadow-sm mb-4 no-print">
                        <div class="card-body p-4">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium d-flex align-items-center">
                                        <i class="bi bi-filter-square-fill me-2 text-primary"></i> Select Election
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="bi bi-calendar-event"></i>
                                        </span>
                                        <select class="form-select shadow-none" name="election" onchange="this.form.submit()">
                                            <option value="">-- All Elections --</option>
                                            <?php 
                                            $elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");
                                            while ($election = $elections->fetch_assoc()): 
                                            ?>
                                            <option value="<?php echo $election['electionID']; ?>" 
                                                <?php echo $electionID == $election['electionID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($election['name']); ?>
                                                (<?php echo date('M Y', strtotime($election['startDate'])); ?>)
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php if ($electionID): ?>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium d-flex align-items-center">
                                        <i class="bi bi-clock-history me-2 text-primary"></i> Generated On
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="bi bi-calendar-check"></i>
                                        </span>
                                        <input type="text" class="form-control shadow-none" 
                                               value="<?php echo date('F j, Y, g:i a'); ?>" readonly>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if ($electionID && $electionDetails): ?>
                    <div class="print-section">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <!-- Total Votes Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-muted d-flex align-items-center mb-3">
                                        <i class="bi bi-check2-square text-primary me-2"></i> Total Votes
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-primary-light me-3">
                                            <i class="bi bi-check2-circle fs-3"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0 fw-bold"><?php echo number_format($totalVotes); ?></h2>
                                            <p class="text-muted mb-0 d-flex align-items-center">
                                                <i class="bi bi-check-all me-1"></i> Votes Cast
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent p-3">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <small>Total ballots submitted</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Unique Voters Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-muted d-flex align-items-center mb-3">
                                        <i class="bi bi-people-fill text-success me-2"></i> Unique Voters
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-success-light me-3">
                                            <i class="bi bi-person-check-fill fs-3"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0 fw-bold"><?php echo number_format($uniqueVoters); ?></h2>
                                            <p class="text-muted mb-0 d-flex align-items-center">
                                                <i class="bi bi-person-fill-check me-1"></i> Distinct Voters
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent p-3">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <small>Students who cast at least one vote</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Candidates Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-muted d-flex align-items-center mb-3">
                                        <i class="bi bi-person-badge-fill text-info me-2"></i> Candidates
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-info-light me-3">
                                            <i class="bi bi-person-video3 fs-3"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0 fw-bold"><?php echo count($voteData); ?></h2>
                                            <p class="text-muted mb-0 d-flex align-items-center">
                                                <i class="bi bi-person-vcard me-1"></i> Active Candidates
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent p-3">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <small>Candidates who received votes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Participation Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-muted d-flex align-items-center mb-3">
                                        <i class="bi bi-graph-up-arrow text-warning me-2"></i> Participation
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-warning-light me-3">
                                            <i class="bi bi-activity fs-3"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0 fw-bold">
                                                <?php 
                                                $totalVoters = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'Active'")->fetch_assoc()['count'];
                                                $participation = $totalVoters > 0 ? round(($uniqueVoters / $totalVoters) * 100) : 0;
                                                echo $participation; ?>%
                                            </h2>
                                            <p class="text-muted mb-0 d-flex align-items-center">
                                                <i class="bi bi-percent me-1"></i> Voter Turnout
                                            </p>
                                            <div class="progress progress-thin mt-2">
                                                <div class="progress-bar bg-<?php echo ($participation > 50) ? 'success' : 'warning'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $participation; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent p-3">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <small><?php echo $uniqueVoters; ?> of <?php echo $totalVoters; ?> eligible voters</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Election Info Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i> 
                                Election Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-bold mb-0 d-flex align-items-center">
                                    <i class="bi bi-award-fill text-warning me-2"></i> 
                                    <?php echo htmlspecialchars($electionDetails['name']); ?>
                                </h5>
                                <span class="badge fs-6 px-3 py-2 bg-<?php 
                                    echo $electionDetails['status'] == 'Ongoing' ? 'success' : 
                                         ($electionDetails['status'] == 'Completed' ? 'secondary' : 'info'); 
                                ?> d-flex align-items-center">
                                    <i class="bi bi-<?php 
                                        echo $electionDetails['status'] == 'Ongoing' ? 'broadcast' : 
                                             ($electionDetails['status'] == 'Completed' ? 'check-circle' : 'clock'); 
                                    ?> me-2"></i>
                                    <?php echo $electionDetails['status']; ?>
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4 mb-md-0">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                                <i class="bi bi-calendar-range-fill fs-1 text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-2 d-flex align-items-center">
                                                <i class="bi bi-calendar2-week me-2"></i> Election Period
                                            </h6>
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-calendar-event text-success me-2"></i>
                                                <span>Start: <?php echo date('M j, Y', strtotime($electionDetails['startDate'])); ?></span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-check text-danger me-2"></i>
                                                <span>End: <?php echo date('M j, Y', strtotime($electionDetails['endDate'])); ?></span>
                                            </div>
                                            <div class="mt-3 small">
                                                <span class="badge bg-light text-dark d-flex align-items-center w-fit-content">
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    Duration: <?php 
                                                        $start = new DateTime($electionDetails['startDate']);
                                                        $end = new DateTime($electionDetails['endDate']);
                                                        $diff = $start->diff($end);
                                                        echo $diff->days + 1; ?> days
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                                <i class="bi bi-people-fill fs-1 text-success"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-2 d-flex align-items-center">
                                                <i class="bi bi-bar-chart-steps me-2"></i> Voting Activity
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-person-check-fill text-success me-2"></i>
                                                    Voted: <?php echo $uniqueVoters; ?> students
                                                </span>
                                                <span class="badge bg-success"><?php echo $participation; ?>%</span>
                                            </div>
                                            <div class="progress progress-thin mb-1">
                                                <div class="progress-bar bg-<?php echo ($participation > 50) ? 'success' : 'warning'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $participation; ?>%">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="d-flex align-items-center">
                                                    <i class="bi bi-person-dash-fill text-danger me-2"></i>
                                                    Not voted: <?php echo $totalVoters - $uniqueVoters; ?> students
                                                </span>
                                                <span class="badge bg-danger"><?php echo 100 - $participation; ?>%</span>
                                            </div>
                                            <div class="mt-3 small">
                                                <span class="badge bg-light text-dark d-flex align-items-center w-fit-content">
                                                    <i class="bi bi-people me-1"></i>
                                                    Total eligible: <?php echo $totalVoters; ?> students
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light p-3">
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                This report shows voting data for the selected election. 
                                <?php if ($electionDetails['status'] == 'Ongoing'): ?>
                                <span class="badge bg-success ms-2">Live data: Updates automatically</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($voteData)): ?>
                        <!-- Vote Breakdown -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-bar-chart-fill text-primary me-2"></i> Vote Breakdown by Candidate
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="position-relative search-box me-2">
                                        <input type="text" class="form-control form-control-sm" id="searchVoters" placeholder="Search voters...">
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item d-flex align-items-center" href="#"><i class="bi bi-sort-alpha-down me-2"></i> Sort by Name</a></li>
                                            <li><a class="dropdown-item d-flex align-items-center" href="#"><i class="bi bi-sort-numeric-down me-2"></i> Sort by Votes</a></li>
                                            <li><a class="dropdown-item d-flex align-items-center" href="#"><i class="bi bi-filter me-2"></i> Filter by Position</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item d-flex align-items-center" href="#"><i class="bi bi-graph-up me-2"></i> Show as Chart</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <?php foreach ($voteData as $candidate): ?>
                                <div class="mb-5">
                                    <div class="d-flex flex-wrap align-items-center mb-4">
                                        <div class="me-3 mb-3 mb-sm-0">
                                            <?php if ($candidate['photo']): ?>
                                            <img src="assets/img/profile/students/<?php echo $candidate['photo']; ?>" 
                                                 class="candidate-photo" 
                                                 alt="<?php echo htmlspecialchars($candidate['candidateName']); ?>">
                                            <?php else: ?>
                                            <div class="candidate-photo bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-person-bounding-box text-muted fs-1"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 mb-3 mb-sm-0">
                                            <h4 class="mb-1 d-flex align-items-center">
                                                <?php echo htmlspecialchars($candidate['candidateName']); ?>
                                                <span class="badge bg-primary ms-2 d-flex align-items-center">
                                                    <i class="bi bi-award me-1"></i>
                                                    <?php echo htmlspecialchars($candidate['position']); ?>
                                                </span>
                                            </h4>
                                            <div class="d-flex align-items-center">
                                                <span class="vote-count-badge d-flex align-items-center">
                                                    <i class="bi bi-check2-circle me-1"></i>
                                                    <?php echo number_format($candidate['voteCount']); ?> votes
                                                </span>
                                                
                                                <?php 
                                                $votePercentage = round(($candidate['voteCount'] / $totalVotes) * 100);
                                                ?>
                                                <div class="ms-3 d-flex align-items-center">
                                                    <div class="progress progress-thin me-2" style="width: 100px; height: 8px;">
                                                        <div class="progress-bar bg-<?php echo ($votePercentage > 50) ? 'success' : 'primary'; ?>"
                                                             role="progressbar" 
                                                             style="width: <?php echo $votePercentage; ?>%">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted small"><?php echo $votePercentage; ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#statsModal<?php echo $candidate['candidateID']; ?>">
                                                <i class="bi bi-graph-up"></i> Statistics
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#voters<?php echo $candidate['candidateID']; ?>">
                                                <i class="bi bi-people"></i> Show Voters
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Statistics Modal -->
                                    <div class="modal fade" id="statsModal<?php echo $candidate['candidateID']; ?>" tabindex="-1" aria-labelledby="statsModalLabel<?php echo $candidate['candidateID']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title" id="statsModalLabel<?php echo $candidate['candidateID']; ?>">
                                                        <i class="bi bi-bar-chart-fill me-2"></i>
                                                        Statistics for <?php echo htmlspecialchars($candidate['candidateName']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row mb-4">
                                                        <div class="col-md-6">
                                                            <div class="card border h-100">
                                                                <div class="card-body">
                                                                    <h5 class="card-title">Vote Distribution</h5>
                                                                    <div class="chart-container" style="position: relative; height:200px;">
                                                                        <canvas id="pieChart<?php echo $candidate['candidateID']; ?>" width="100%" height="100%"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card border h-100">
                                                                <div class="card-body">
                                                                    <h5 class="card-title">Vote Timeline</h5>
                                                                    <div class="chart-container" style="position: relative; height:200px;">
                                                                        <canvas id="lineChart<?php echo $candidate['candidateID']; ?>" width="100%" height="100%"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="card border">
                                                                <div class="card-body text-center">
                                                                    <h5 class="text-primary mb-1"><?php echo number_format($candidate['voteCount']); ?></h5>
                                                                    <p class="text-muted mb-0 small">Total Votes</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="card border">
                                                                <div class="card-body text-center">
                                                                    <h5 class="text-primary mb-1"><?php echo $totalVotes > 0 ? round(($candidate['voteCount'] / $totalVotes) * 100) : 0; ?>%</h5>
                                                                    <p class="text-muted mb-0 small">Percentage</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="card border">
                                                                <div class="card-body text-center">
                                                                    <?php
                                                                    // Calculate votes by department
                                                                    $departments = [];
                                                                    foreach ($candidate['votes'] as $vote) {
                                                                        if (isset($vote['voterDepartment'])) {
                                                                            $dept = $vote['voterDepartment'];
                                                                            if (!isset($departments[$dept])) {
                                                                                $departments[$dept] = 0;
                                                                            }
                                                                            $departments[$dept]++;
                                                                        }
                                                                    }
                                                                    
                                                                    // Set a default department if none exists
                                                                    $topDept = 'None';
                                                                    
                                                                    // Only try to get top department if we have data
                                                                    if (!empty($departments)) {
                                                                        arsort($departments);
                                                                        // Get first key safely using array_keys
                                                                        $deptKeys = array_keys($departments);
                                                                        if (!empty($deptKeys)) {
                                                                            $topDept = $deptKeys[0];
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <h5 class="text-primary mb-1"><?php echo htmlspecialchars($topDept); ?></h5>
                                                                    <p class="text-muted mb-0 small">Top Department</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <h5 class="mt-4 mb-3">Department Distribution</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <th>Votes</th>
                                                                    <th>Percentage</th>
                                                                    <th>Progress</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($departments as $dept => $count): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($dept); ?></td>
                                                                    <td><?php echo $count; ?></td>
                                                                    <td><?php echo $candidate['voteCount'] > 0 ? round(($count / $candidate['voteCount']) * 100) : 0; ?>%</td>
                                                                    <td>
                                                                        <div class="progress" style="height: 6px;">
                                                                            <div class="progress-bar" role="progressbar" 
                                                                                 style="width: <?php echo $candidate['voteCount'] > 0 ? ($count / $candidate['voteCount']) * 100 : 0; ?>%"></div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary" onclick="window.print()">
                                                        <i class="bi bi-printer me-1"></i> Print Report
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="collapse show" id="voters<?php echo $candidate['candidateID']; ?>">
                                        <div class="card card-body border-0 bg-light">
                                            <h5 class="d-flex align-items-center mb-3">
                                                <i class="bi bi-people-fill me-2 text-primary"></i> Voters
                                                <span class="badge bg-primary ms-2 rounded-pill"><?php echo count($candidate['votes']); ?></span>
                                            </h5>
                                            
                                            <div class="row">
                                                <?php foreach ($candidate['votes'] as $vote): ?>
                                                <div class="col-md-6 vote-item-container">
                                                    <div class="vote-item">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <?php 
                                                                // Get voter photo from the database, fallback to default if empty
                                                                $voterPhoto = !empty($vote['voterPhoto']) ? $vote['voterPhoto'] : 'default-user.jpg';
                                                                ?>
                                                                <div class="position-relative">
                                                                    <img src="assets/img/profile/students/<?php echo $voterPhoto; ?>" 
                                                                         class="user-avatar" 
                                                                         alt="<?php echo htmlspecialchars($vote['voterName']); ?>"
                                                                         onerror="this.src='assets/img/aristo.png';"
                                                                         style="cursor: pointer;"
                                                                         data-bs-toggle="modal" 
                                                                         data-bs-target="#voterModal<?php echo $vote['studentID']; ?>">
                                                                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1">
                                                                        <i class="bi bi-check-lg text-white"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h6 class="mb-1 d-flex align-items-center">
                                                                    <a href="#" class="text-decoration-none" 
                                                                       data-bs-toggle="modal" 
                                                                       data-bs-target="#voterModal<?php echo $vote['studentID']; ?>">
                                                                        <?php echo htmlspecialchars($vote['voterName']); ?>
                                                                    </a>
                                                                </h6>
                                                                <div class="d-flex align-items-center text-muted small">
                                                                    <i class="bi bi-buildings department-icon icon"></i>
                                                                    <?php echo htmlspecialchars($vote['voterDepartment']); ?> 
                                                                    <span class="mx-1">•</span>
                                                                    <i class="bi bi-clock me-1"></i>
                                                                    <?php echo date('M j, Y g:i a', strtotime($vote['timestamp'])); ?>
                                                                </div>
                                                            </div>
                                                            <div class="ms-2 dropdown">
                                                                <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                                                    <i class="bi bi-three-dots-vertical"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#voterModal<?php echo $vote['studentID']; ?>">
                                                                        <i class="bi bi-person-vcard me-2"></i> View Profile</a></li>
                                                                    <li><a class="dropdown-item d-flex align-items-center" href="#">
                                                                        <i class="bi bi-envelope me-2"></i> Message</a></li>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li><a class="dropdown-item d-flex align-items-center text-danger" href="#">
                                                                        <i class="bi bi-x-circle me-2"></i> Invalidate Vote</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Voter Profile Modal -->
                                                    <div class="modal fade" id="voterModal<?php echo $vote['studentID']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-light">
                                                                    <h5 class="modal-title">
                                                                        <i class="bi bi-person-badge me-2"></i>Voter Profile
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="text-center mb-4">
                                                                        <img src="assets/img/profile/students/<?php echo $voterPhoto; ?>" 
                                                                             class="rounded-circle img-thumbnail mb-3" 
                                                                             style="width: 150px; height: 150px; object-fit: cover;"
                                                                             alt="<?php echo htmlspecialchars($vote['voterName']); ?>"
                                                                             onerror="this.src='assets/img/aristo.png';">
                                                                        <h4><?php echo htmlspecialchars($vote['voterName']); ?></h4>
                                                                        <span class="badge bg-info rounded-pill">
                                                                            <i class="bi bi-mortarboard-fill me-1"></i>
                                                                            Student
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="list-group mb-4">
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <i class="bi bi-buildings me-2 text-primary"></i>
                                                                                <span>Department</span>
                                                                            </div>
                                                                            <strong><?php echo htmlspecialchars($vote['voterDepartment']); ?></strong>
                                                                        </div>
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <i class="bi bi-person-fill me-2 text-primary"></i>
                                                                                <span>Student ID</span>
                                                                            </div>
                                                                            <strong><?php echo $vote['studentID']; ?></strong>
                                                                        </div>
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <i class="bi bi-calendar-check me-2 text-primary"></i>
                                                                                <span>Last Vote</span>
                                                                            </div>
                                                                            <strong><?php echo date('M j, Y g:i a', strtotime($vote['timestamp'])); ?></strong>
                                                                        </div>
                                                                        
                                                                        <?php
                                                                        // Count all votes by this voter in the election
                                                                        $voterVoteCount = $conn->prepare("
                                                                            SELECT COUNT(*) as count 
                                                                            FROM votes
                                                                            WHERE electionID = ? AND studentID = ?
                                                                        ");
                                                                        $voterVoteCount->bind_param("ii", $electionID, $vote['studentID']);
                                                                        $voterVoteCount->execute();
                                                                        $countResult = $voterVoteCount->get_result()->fetch_assoc();
                                                                        $totalVotesByVoter = $countResult['count'];
                                                                        ?>
                                                                        
                                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <i class="bi bi-check-circle me-2 text-primary"></i>
                                                                                <span>Total Votes Cast</span>
                                                                            </div>
                                                                            <strong><?php echo $totalVotesByVoter; ?></strong>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <?php
                                                                    // Get other candidates this voter voted for
                                                                    $otherVotes = $conn->prepare("
                                                                        SELECT c.candidateID, p.title as position, st.name as candidateName  
                                                                        FROM votes v
                                                                        JOIN candidates c ON v.candidateID = c.candidateID
                                                                        JOIN positions p ON c.positionID = p.positionID
                                                                        JOIN students st ON c.studentID = st.studentID
                                                                        WHERE v.electionID = ? AND v.studentID = ?
                                                                    ");
                                                                    $otherVotes->bind_param("ii", $electionID, $vote['studentID']);
                                                                    $otherVotes->execute();
                                                                    $otherVotesResult = $otherVotes->get_result();
                                                                    
                                                                    if ($otherVotesResult->num_rows > 0):
                                                                    ?>
                                                                    <h6 class="mb-3"><i class="bi bi-check2-all me-1"></i>Votes Cast</h6>
                                                                    <table class="table table-sm table-hover">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>Position</th>
                                                                                <th>Candidate</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php while($row = $otherVotesResult->fetch_assoc()): ?>
                                                                            <tr>
                                                                                <td><?php echo htmlspecialchars($row['position']); ?></td>
                                                                                <td><?php echo htmlspecialchars($row['candidateName']); ?></td>
                                                                            </tr>
                                                                            <?php endwhile; ?>
                                                                        </tbody>
                                                                    </table>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <a href="#" class="btn btn-primary">
                                                                        <i class="bi bi-envelope me-1"></i>Contact
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Votes Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="py-5">
                                    <div class="display-1 text-muted mb-4">
                                        <i class="bi bi-inbox-fill"></i>
                                    </div>
                                    <h4 class="text-primary mb-3">No votes recorded yet</h4>
                                    <p class="text-muted mb-4 w-75 mx-auto">Voting records will appear here once votes are cast in this election. Check back later or select a different election.</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-primary d-flex align-items-center" onclick="location.reload()">
                                            <i class="bi bi-arrow-clockwise me-2"></i> Refresh
                                        </button>
                                        <a href="elections.php" class="btn btn-outline-secondary d-flex align-items-center">
                                            <i class="bi bi-list-check me-2"></i> View All Elections
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    </div>
                    
                    <?php elseif ($electionID): ?>
                    <!-- Election Not Found -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="py-5">
                                <div class="display-1 text-warning mb-4">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <h4 class="text-warning mb-3">Election Not Found</h4>
                                <p class="text-muted mb-4 w-75 mx-auto">The election you selected doesn't exist or may have been removed. Please select a different election from the list.</p>
                                <a href="votes.php" class="btn btn-outline-primary d-flex align-items-center mx-auto" style="width: fit-content;">
                                    <i class="bi bi-arrow-left me-2"></i> Back to All Elections
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Select Election -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="py-5">
                                <div class="display-1 text-primary mb-4">
                                    <i class="bi bi-hand-index-thumb"></i>
                                </div>
                                <h4 class="text-primary mb-3">Select an Election</h4>
                                <p class="text-muted mb-4 w-75 mx-auto">Choose an election from the dropdown above to view detailed voting records and statistics.</p>
                                <button class="btn btn-primary d-flex align-items-center mx-auto" style="width: fit-content;" onclick="document.querySelector('select[name=\'election\']').focus()">
                                    <i class="bi bi-filter-circle-fill me-2"></i> Select Election
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
    
    <!-- Chart.js for statistics visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add a small delay to display fade-in effects
        setTimeout(() => {
            document.querySelectorAll('.card').forEach(card => {
                card.style.opacity = '1';
            });
        }, 100);
        
        // Search functionality for voters
        const searchInput = document.getElementById('searchVoters');
        const voterItems = document.querySelectorAll('.vote-item-container');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                if (searchTerm === '') {
                    voterItems.forEach(item => {
                        item.style.display = '';
                    });
                    return;
                }
                
                voterItems.forEach(item => {
                    const name = item.querySelector('h6').textContent.toLowerCase();
                    const department = item.querySelector('.text-muted').textContent.toLowerCase();
                    const isVisible = name.includes(searchTerm) || department.includes(searchTerm);
                    
                    if (isVisible) {
                        item.style.display = '';
                        // Highlight the matched text
                        if (searchTerm.length > 1) {
                            const nameElement = item.querySelector('h6');
                            nameElement.innerHTML = nameElement.textContent.replace(
                                new RegExp(searchTerm, 'gi'),
                                match => `<span class="bg-warning bg-opacity-50">${match}</span>`
                            );
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
        
        // Initialize charts for statistics modals
        <?php foreach ($voteData as $candidate): ?>
        // Only create charts if the modal exists
        if (document.getElementById('pieChart<?php echo $candidate['candidateID']; ?>')) {
            // Get department distribution for pie chart
            <?php 
            $departments = [];
            foreach ($candidate['votes'] as $vote) {
                $dept = $vote['voterDepartment'];
                if (!isset($departments[$dept])) {
                    $departments[$dept] = 0;
                }
                $departments[$dept]++;
            }
            ?>
            
            // Pie chart for department distribution
            const pieData<?php echo $candidate['candidateID']; ?> = {
                labels: [<?php 
                    $labels = [];
                    foreach ($departments as $dept => $count) {
                        $labels[] = "'$dept'";
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    data: [<?php echo implode(',', $departments); ?>],
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#5a5c69', '#6610f2', '#fd7e14', '#20c9a6', '#6f42c1'
                    ],
                    hoverOffset: 4
                }]
            };
            
            const pieCtx<?php echo $candidate['candidateID']; ?> = document.getElementById('pieChart<?php echo $candidate['candidateID']; ?>').getContext('2d');
            new Chart(pieCtx<?php echo $candidate['candidateID']; ?>, {
                type: 'pie',
                data: pieData<?php echo $candidate['candidateID']; ?>,
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12
                            }
                        }
                    }
                }
            });
            
            // Timeline chart - votes over time
            <?php 
            // Group votes by date for timeline
            $timeline = [];
            $dates = [];
            
            // Sort votes by timestamp
            $sortedVotes = $candidate['votes'];
            usort($sortedVotes, function($a, $b) {
                return strtotime($a['timestamp']) - strtotime($b['timestamp']);
            });
            
            $cumulativeCount = 0;
            foreach ($sortedVotes as $vote) {
                $date = date('M j', strtotime($vote['timestamp']));
                if (!isset($timeline[$date])) {
                    $timeline[$date] = 0;
                    $dates[] = $date;
                }
                $cumulativeCount++;
                $timeline[$date] = $cumulativeCount;
            }
            ?>
            
            const timelineData<?php echo $candidate['candidateID']; ?> = {
                labels: [<?php echo "'" . implode("','", $dates) . "'"; ?>],
                datasets: [{
                    label: 'Cumulative Votes',
                    data: [<?php echo implode(',', $timeline); ?>],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            };
            
            const lineCtx<?php echo $candidate['candidateID']; ?> = document.getElementById('lineChart<?php echo $candidate['candidateID']; ?>').getContext('2d');
            new Chart(lineCtx<?php echo $candidate['candidateID']; ?>, {
                type: 'line',
                data: timelineData<?php echo $candidate['candidateID']; ?>,
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        <?php endforeach; ?>
        
        // Print functionality with custom styling
        const printBtn = document.getElementById('printBtn');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                // Add a print-friendly message
                const printMessage = document.createElement('div');
                printMessage.className = 'print-message text-center mb-4';
                printMessage.innerHTML = `
                    <h4 class="text-primary mb-2">SmartVote - Election Results</h4>
                    <p class="small text-muted">Generated on ${new Date().toLocaleString()}</p>
                    <hr>
                `;
                
                const printSection = document.querySelector('.print-section');
                printSection.prepend(printMessage);
                
                // Print the document
                window.print();
                
                // Remove the message after printing
                setTimeout(() => {
                    printSection.removeChild(printMessage);
                }, 500);
            });
        }
        
        // Export button functionality
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
            exportBtn.addEventListener('click', function() {
                exportModal.show();
            });
        }
        
        // Export format change handler
        const formatInputs = document.querySelectorAll('input[name="exportFormat"]');
        formatInputs.forEach(input => {
            input.addEventListener('change', function() {
                const format = this.value;
                const exportBtn = document.querySelector('#exportModal .btn-success');
                
                // Update button icon based on format
                const icon = exportBtn.querySelector('i');
                icon.className = 'bi ';
                
                switch(format) {
                    case 'csv':
                        icon.className += 'bi-filetype-csv me-2';
                        break;
                    case 'excel':
                        icon.className += 'bi-file-earmark-excel me-2';
                        break;
                    case 'pdf':
                        icon.className += 'bi-file-earmark-pdf me-2';
                        break;
                    case 'json':
                        icon.className += 'bi-filetype-json me-2';
                        break;
                    default:
                        icon.className += 'bi-cloud-download-fill me-2';
                }
            });
        });
        
        // Show/hide voters sections
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
            button.addEventListener('click', function() {
                const target = this.getAttribute('data-bs-target');
                const icon = this.querySelector('i.bi');
                
                if (document.querySelector(target).classList.contains('show')) {
                    icon.className = 'bi bi-people'; // Change to 'show' icon
                    this.innerHTML = '<i class="bi bi-people"></i> Show Voters';
                } else {
                    icon.className = 'bi bi-people-fill'; // Change to 'hide' icon
                    this.innerHTML = '<i class="bi bi-people-fill"></i> Hide Voters';
                }
            });
        });
        
        // Add tooltips to all elements with data-bs-toggle="tooltip"
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>