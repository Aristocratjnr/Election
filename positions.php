<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

// Check if display_order column exists, add if not
try {
    $checkColumnQuery = "SHOW COLUMNS FROM positions LIKE 'display_order'";
    $columnExists = $conn->query($checkColumnQuery)->num_rows > 0;
    
    if (!$columnExists) {
        // Add display_order column
        $addColumnQuery = "ALTER TABLE positions ADD COLUMN display_order INT DEFAULT 0";
        $conn->query($addColumnQuery);
        
        // Update display_order to match positionID initially
        $updateOrderQuery = "UPDATE positions SET display_order = positionID";
        $conn->query($updateOrderQuery);
    }
} catch (Exception $e) {
    // Silently handle error - we'll continue anyway
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_position'])) {
        $electionID = $_POST['electionID'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $maxVotes = $_POST['maxVotes'] ?? 1;
        $display_order = $_POST['display_order'] ?? 0;
        
        // Check if position with the same title already exists for this election
        $checkTitle = $conn->prepare("SELECT COUNT(*) as count FROM positions WHERE electionID = ? AND LOWER(title) = LOWER(?)");
        $checkTitle->bind_param("is", $electionID, $title);
        $checkTitle->execute();
        $titleExists = $checkTitle->get_result()->fetch_assoc()['count'] > 0;
        $checkTitle->close();
        
        if ($titleExists) {
            $error = "A position with this title already exists for this election. Please use a different title.";
        } else {
            // If display_order is not set or is 0, set it to the max+1 for this election
            if ($display_order == 0) {
                $maxOrderQuery = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM positions WHERE electionID = ?");
                $maxOrderQuery->bind_param("i", $electionID);
                $maxOrderQuery->execute();
                $display_order = $maxOrderQuery->get_result()->fetch_assoc()['next_order'];
            }
            
            // Check if positions table has a status column
            $checkStatusCol = $conn->query("SHOW COLUMNS FROM positions LIKE 'status'");
            if ($checkStatusCol->num_rows > 0) {
                // Include status field in the query
                $stmt = $conn->prepare("INSERT INTO positions (electionID, title, description, maxVotes, display_order, status) VALUES (?, ?, ?, ?, ?, 'Approved')");
                $stmt->bind_param("issii", $electionID, $title, $description, $maxVotes, $display_order);
            } else {
                // Original query without status
                $stmt = $conn->prepare("INSERT INTO positions (electionID, title, description, maxVotes, display_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issii", $electionID, $title, $description, $maxVotes, $display_order);
            }
            
            $stmt->execute();
            $success = "Position added successfully!";
        }
    } elseif (isset($_POST['update_position'])) {
        $positionID = $_POST['positionID'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $maxVotes = $_POST['maxVotes'];
        $display_order = $_POST['display_order'] ?? 0;
        
        // Check if position with the same title already exists for this election
        $checkTitle = $conn->prepare("SELECT COUNT(*) as count FROM positions p1 
            JOIN positions p2 ON p1.electionID = p2.electionID 
            WHERE p2.positionID = ? AND p1.positionID != ? 
            AND LOWER(p1.title) = LOWER(?)");
        $checkTitle->bind_param("iis", $positionID, $positionID, $title);
        $checkTitle->execute();
        $titleExists = $checkTitle->get_result()->fetch_assoc()['count'] > 0;
        $checkTitle->close();
        
        if ($titleExists) {
            $error = "A position with this title already exists for this election. Please use a different title.";
        } else {
            // Check if positions table has a status column
            $checkStatusCol = $conn->query("SHOW COLUMNS FROM positions LIKE 'status'");
            if ($checkStatusCol->num_rows > 0) {
                // Include status field in the query
                $stmt = $conn->prepare("UPDATE positions SET title = ?, description = ?, maxVotes = ?, display_order = ?, status = 'Approved' WHERE positionID = ?");
                $stmt->bind_param("ssiii", $title, $description, $maxVotes, $display_order, $positionID);
            } else {
                // Original query without status
                $stmt = $conn->prepare("UPDATE positions SET title = ?, description = ?, maxVotes = ?, display_order = ? WHERE positionID = ?");
                $stmt->bind_param("ssiii", $title, $description, $maxVotes, $display_order, $positionID);
            }
            
            $stmt->execute();
            
            // Clear any caches that might affect position display
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            $success = "Position updated successfully!";
        }
    } elseif (isset($_POST['delete_position'])) {
        $positionID = $_POST['positionID'];
        
        // First check if there are any candidates associated with this position
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM candidates WHERE positionID = ?");
        $check_stmt->bind_param("i", $positionID);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $error = "Cannot delete position: There are candidates associated with this position. Please delete the candidates first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM positions WHERE positionID = ?");
            $stmt->bind_param("i", $positionID);
            if ($stmt->execute()) {
                $success = "Position deleted successfully!";
            } else {
                $error = "Error deleting position: " . $conn->error;
            }
        }
    } elseif (isset($_POST['move_position'])) {
        $positionID = $_POST['positionID'];
        $direction = $_POST['direction'];
        $electionID = $_POST['electionID'];
        
        // Get current display_order
        $stmt = $conn->prepare("SELECT display_order FROM positions WHERE positionID = ?");
        $stmt->bind_param("i", $positionID);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();
        $current_order = $current['display_order'];
        
        if ($direction === 'up') {
            // Get the position above this one
            $stmt = $conn->prepare("
                SELECT positionID, display_order 
                FROM positions 
                WHERE electionID = ? AND display_order < ? 
                ORDER BY display_order DESC 
                LIMIT 1
            ");
            $stmt->bind_param("ii", $electionID, $current_order);
        } else { // down
            // Get the position below this one
            $stmt = $conn->prepare("
                SELECT positionID, display_order 
                FROM positions 
                WHERE electionID = ? AND display_order > ? 
                ORDER BY display_order ASC 
                LIMIT 1
            ");
            $stmt->bind_param("ii", $electionID, $current_order);
        }
        
        $stmt->execute();
        $swap = $stmt->get_result()->fetch_assoc();
        
        if ($swap) {
            // Swap the display orders
            $stmt = $conn->prepare("UPDATE positions SET display_order = ? WHERE positionID = ?");
            $stmt->bind_param("ii", $swap['display_order'], $positionID);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE positions SET display_order = ? WHERE positionID = ?");
            $stmt->bind_param("ii", $current_order, $swap['positionID']);
            $stmt->execute();
            
            $success = "Position order updated successfully!";
        }
    }
}

// Get all elections for dropdown
$elections = $conn->query("SELECT * FROM elections ORDER BY startDate DESC");

// Get all positions
$positions = $conn->query("
    SELECT p.*, e.name as electionName 
    FROM positions p
    JOIN elections e ON p.electionID = e.electionID
    ORDER BY e.electionID, p.display_order, p.positionID
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Positions - EMS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #224abe;
            --secondary-color: #f8f9fc;
            --text-primary: #5a5c69;
            --text-secondary: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-primary);
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1;
            position: fixed;
            height: 100vh;
        }
        
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 1.5rem;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top-left-radius: 0.75rem !important;
            border-top-right-radius: 0.75rem !important;
        }
        
        .header-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }
        
       
        
        .btn-secondary {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }
        
       
        
        .table {
            color: var(--text-primary);
        }
        
        .table th {
            font-weight: 600;
            border-top: none;
            background-color: #f8f9fc;
        }
        
       
        
        .badge-status {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        
        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1.2rem 1.5rem;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .alert {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            padding: 1.5rem 1rem;
        }
        
        .search-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-wrapper .form-control {
            padding-left: 2.5rem;
            border-radius: 10rem;
            height: calc(1.5em + 0.75rem + 2px);
            border: 1px solid #e3e6f0;
            transition: all 0.2s;
        }
        
        .search-wrapper .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .search-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background-color: transparent;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }
        
       
        
        .card-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        
        
        .card-stats-icon {
            font-size: 2rem;
            padding: 1rem;
            border-radius: 50%;
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
            transition: transform 0.2s;
        }
      
        
        .card-stats-info {
            text-align: right;
        }
        
        .card-stats-number {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .card-stats-label {
            color: var(--text-secondary);
            margin-bottom: 0;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e3e6f0;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
            background-color: rgba(78, 115, 223, 0.1);
            border: 1px solid #e3e6f0;
            color: var(--primary-color);
        }
        
        .input-group .form-control {
            border-radius: 0 0.5rem 0.5rem 0;
        }
        
        .dropdown-item.active, .dropdown-item:active {
            background-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.2s;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .btn-outline-danger {
            transition: all 0.2s;
        }
        
        .btn-outline-danger:hover {
            color: white;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
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

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }
        body.dark-mode .card,
        body.dark-mode .modal-content,
        body.dark-mode .page-header,
        body.dark-mode .card-stats {
            background-color: #2c2c2c;
            color: #e0e0e0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(255, 255, 255, 0.05);
            border: 1px solid #444;
        }
        body.dark-mode .card-header,
        body.dark-mode .modal-footer {
            background-color: #333;
            border-color: #444;
        }
        body.dark-mode .modal-header {
            background-color: var(--primary-dark); /* Darker primary for header */
            border-color: #444;
        }
        body.dark-mode .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        body.dark-mode .table {
            color: #e0e0e0;
        }
        body.dark-mode .table th {
            background-color: #3a3a3a;
            color: #f0f0f0;
            border-color: #444;
        }
        body.dark-mode .table td {
            background-color: #2c2c2c;
            border-color: #444;
        }
        body.dark-mode .table tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.08);
        }
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #3a3a3a;
            color: #e0e0e0;
            border-color: #555;
        }
        body.dark-mode .form-control::placeholder {
            color: #888;
        }
        body.dark-mode .input-group-text {
            background-color: #333;
            border-color: #555;
            color: #ccc;
        }
        body.dark-mode .text-primary {
            color: #6ca5ff !important;
        }
        body.dark-mode .text-muted {
            color: #aaa !important;
        }
        body.dark-mode .text-gray-800 {
            color: #e0e0e0 !important;
        }
        body.dark-mode .breadcrumb-item a {
            color: #6ca5ff;
        }
        body.dark-mode .breadcrumb-item.active {
            color: #aaa;
        }
        body.dark-mode .badge.bg-info {
            background-color: #36b9cc !important;
            color: #fff !important;
        }
        body.dark-mode .badge.bg-primary {
            background-color: #4e73df !important;
            color: #fff !important;
        }
        body.dark-mode .badge.bg-secondary {
            background-color: #555 !important;
            color: #e0e0e0 !important;
        }
        body.dark-mode .btn-outline-primary {
            color: #6ca5ff;
            border-color: #6ca5ff;
        }
        body.dark-mode .btn-outline-primary:hover {
            background-color: #6ca5ff;
            color: #1a1a1a;
            border-color: #6ca5ff;
        }
        body.dark-mode .btn-outline-secondary {
            color: #ccc;
            border-color: #555;
        }
        body.dark-mode .btn-outline-secondary:hover {
            background-color: #444;
            color: #fff;
            border-color: #444;
        }
        body.dark-mode .btn-outline-danger {
             color: #e74a3b;
             border-color: #e74a3b;
        }
        body.dark-mode .btn-outline-danger:hover {
            background-color: #e74a3b;
            color: #fff;
        }
        body.dark-mode .dataTables_wrapper .dataTables_length select,
        body.dark-mode .dataTables_wrapper .dataTables_filter input {
            background-color: #3a3a3a;
            color: #e0e0e0;
            border-color: #555;
        }
        body.dark-mode .dataTables_wrapper .dataTables_info,
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #ccc !important;
        }
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #666 !important;
        }
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #444;
            border-color: #555;
            color: #fff !important;
        }
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6ca5ff;
            border-color: #6ca5ff;
            color: #1a1a1a !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <?php include 'includes/header.php'; ?><br><br>
            <div class="main-content">
                <nav aria-label="breadcrumb" class="mb-5">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-person-badge me-1"></i>Manage Positions</li>
                    </ol>
                </nav>
                
                <div class="page-header">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-list-check me-2"></i>Manage Positions</h1>
                        <p class="mb-0 text-muted"><i class="bi bi-info-circle me-1"></i>Create and manage election positions</p>
                    </div>
                    <div>
                        <a href="position_order.php" class="btn btn-outline-primary shadow-sm me-2">
                            <i class="bi bi-arrows-move me-1"></i> Manage Order
                        </a>
                        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Position
                        </button>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card-stats bg-white">
                            <div class="card-stats-icon">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div class="card-stats-info">
                                <p class="card-stats-number">
                                    <?php 
                                    $total = $conn->query("SELECT COUNT(*) as total FROM positions")->fetch_assoc();
                                    echo $total['total'];
                                    ?>
                                </p>
                                <p class="card-stats-label"><i class="bi bi-clipboard-data me-1"></i>Total Positions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card-stats bg-white">
                            <div class="card-stats-icon">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <div class="card-stats-info">
                                <p class="card-stats-number">
                                    <?php 
                                    $activeElections = $conn->query("SELECT COUNT(DISTINCT electionID) as total FROM positions")->fetch_assoc();
                                    echo $activeElections['total'];
                                    ?>
                                </p>
                                <p class="card-stats-label"><i class="bi bi-diagram-3 me-1"></i>Elections With Positions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card-stats bg-white">
                            <div class="card-stats-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="card-stats-info">
                            <p class="card-stats-number">
                                <?php 
                                $avgVotes = $conn->query("SELECT COALESCE(AVG(maxVotes), 0) as avg FROM positions")->fetch_assoc();
                                echo number_format($avgVotes['avg'], 1);
                                ?>
                            </p>
                                <p class="card-stats-label"><i class="bi bi-calculator me-1"></i>Avg. Max Votes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-list-check header-icon"></i>
                            <h5 class="mb-0">Current Positions</h5>
                        </div>
                        <div class="search-wrapper d-none d-md-block">
                            <input type="text" id="positionSearch" class="form-control" placeholder="Search positions...">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="positionsTable">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-hash me-1"></i>ID</th>
                                        <th><i class="bi bi-card-heading me-1"></i>Position Title</th>
                                        <th><i class="bi bi-calendar-event me-1"></i>Election</th>
                                        <th><i class="bi bi-check-square me-1"></i>Max Votes</th>
                                        <th><i class="bi bi-arrow-down-up me-1"></i>Order</th>
                                        <th><i class="bi bi-text-paragraph me-1"></i>Description</th>
                                        <th class="text-center"><i class="bi bi-gear me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($position = $positions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $position['positionID']; ?></td>
                                        <td>
                                            <strong><i class="bi bi-person-workspace me-1"></i><?php echo htmlspecialchars($position['title']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><i class="bi bi-calendar2-event me-1"></i><?php echo htmlspecialchars($position['electionName']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><i class="bi bi-123 me-1"></i><?php echo $position['maxVotes']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><i class="bi bi-arrow-down-up me-1"></i><?php echo $position['display_order']; ?></span>
                                        </td>
                                        <td class="description-cell" title="<?php echo htmlspecialchars($position['description']); ?>">
                                            <i class="bi bi-info-circle me-1"></i><?php echo htmlspecialchars($position['description']); ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons justify-content-center">
                                                <div class="btn-group btn-group-sm me-1">
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="positionID" value="<?php echo $position['positionID']; ?>">
                                                        <input type="hidden" name="electionID" value="<?php echo $position['electionID']; ?>">
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" name="move_position" class="btn btn-sm btn-outline-secondary" title="Move Up">
                                                            <i class="bi bi-arrow-up"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="positionID" value="<?php echo $position['positionID']; ?>">
                                                        <input type="hidden" name="electionID" value="<?php echo $position['electionID']; ?>">
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" name="move_position" class="btn btn-sm btn-outline-secondary" title="Move Down">
                                                            <i class="bi bi-arrow-down"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPositionModal"
                                                        data-id="<?php echo $position['positionID']; ?>"
                                                        data-title="<?php echo htmlspecialchars($position['title']); ?>"
                                                        data-description="<?php echo htmlspecialchars($position['description']); ?>"
                                                        data-maxvotes="<?php echo $position['maxVotes']; ?>"
                                                        data-display-order="<?php echo $position['display_order']; ?>">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="positionID" value="<?php echo $position['positionID']; ?>">
                                                    <button type="submit" name="delete_position" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this position? This action cannot be undone.')">
                                                        <i class="bi bi-trash-fill"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1" aria-labelledby="addPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="positionForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPositionModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Add New Position
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="electionID" class="form-label"><i class="bi bi-calendar3 me-1"></i>Election</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-megaphone"></i></span>
                                <select class="form-select" id="electionID" name="electionID" required>
                                    <option value="">Select Election</option>
                                    <?php 
                                    // Reset the elections result pointer
                                    $elections->data_seek(0);
                                    while ($election = $elections->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $election['electionID']; ?>"><?php echo htmlspecialchars($election['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label"><i class="bi bi-type me-1"></i>Position Title</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-bookmark"></i></span>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter position title" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label"><i class="bi bi-file-text me-1"></i>Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter position description"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="maxVotes" class="form-label"><i class="bi bi-ui-checks me-1"></i>Maximum Votes</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="number" 
                                       class="form-control" 
                                       id="maxVotes" 
                                       name="maxVotes" 
                                       value="<?= isset($position) ? $position['maxVotes'] : '1' ?>" 
                                       min="1" 
                                       max="999"
                                       placeholder="Enter maximum votes allowed"
                                       required>
                            </div>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Maximum number of candidates that can be voted for this position.</div>
                        </div>
                        <div class="mb-3">
                            <label for="display_order" class="form-label"><i class="bi bi-sort-numeric-down me-1"></i>Display Order</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                                <input type="number" 
                                       class="form-control" 
                                       id="display_order" 
                                       name="display_order" 
                                       value="<?= isset($position) ? $position['display_order'] : '0' ?>" 
                                       min="0" 
                                       max="999"
                                       placeholder="Enter display order (0 = default)"
                                       required>
                            </div>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>The order in which this position will be displayed (lower numbers first).</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                        <button type="submit" name="add_position" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <div class="modal fade" id="editPositionModal" tabindex="-1" aria-labelledby="editPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPositionModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Edit Position
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_positionID" name="positionID">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label"><i class="bi bi-type me-1"></i>Position Title</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-bookmark"></i></span>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label"><i class="bi bi-file-text me-1"></i>Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_maxVotes" class="form-label"><i class="bi bi-ui-checks me-1"></i>Max Votes Allowed</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                                <input type="number" class="form-control" id="edit_maxVotes" name="maxVotes" min="1" required>
                            </div>
                            <small class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>Number of candidates a voter can select for this position</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_display_order" class="form-label"><i class="bi bi-sort-numeric-down me-1"></i>Display Order</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                                <input type="number" class="form-control" id="edit_display_order" name="display_order" min="0" required>
                            </div>
                            <small class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>The order in which this position will be displayed</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                        <button type="submit" name="update_position" class="btn btn-primary">
                            <i class="bi bi-save2 me-1"></i> Update Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#positionsTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                responsive: true,
                language: {
                    search: "<i class='bi bi-search'></i>",
                    searchPlaceholder: "Search positions...",
                    infoFiltered: "<span class='text-muted'>(filtered from _MAX_ total positions)</span>",
                    emptyTable: "<i class='bi bi-exclamation-triangle text-warning'></i> No positions found",
                    zeroRecords: "<i class='bi bi-search'></i> No matching positions found",
                    paginate: {
                        first: "<i class='bi bi-chevron-double-left'></i>",
                        previous: "<i class='bi bi-chevron-left'></i>",
                        next: "<i class='bi bi-chevron-right'></i>",
                        last: "<i class='bi bi-chevron-double-right'></i>"
                    }
                }
            });
            
            // Style DataTable search input
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
            
            // Handle edit button clicks
            $('.edit-btn').on('click', function() {
                $('#edit_positionID').val($(this).data('id'));
                $('#edit_title').val($(this).data('title'));
                $('#edit_description').val($(this).data('description'));
                $('#edit_maxVotes').val($(this).data('maxvotes'));
                $('#edit_display_order').val($(this).data('display-order'));
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert-success').fadeOut('slow');
            }, 5000);
            
            // Enable tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Simple search functionality
            $("#positionSearch").on("keyup", function() {
                const value = $(this).val().toLowerCase();
                $("#positionsTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Add animation effects
            $('.card').addClass('animate__animated animate__fadeIn');
            
            // Form validation
            document.getElementById('positionForm').addEventListener('submit', function(e) {
                const maxVotes = document.getElementById('maxVotes').value;
                if (maxVotes < 1) {
                    e.preventDefault();
                    alert('Maximum votes must be at least 1');
                    return false;
                }
            });
        });
    </script>
  
</body>
</html>