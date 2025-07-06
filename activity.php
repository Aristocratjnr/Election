<?php
// Secure session initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Check if admin is logged in
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit();
}

// Check admin role (if needed)
if ($_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Database connection
require_once 'configs/dbconnection.php';

// Initial variables
$admin_id = $_SESSION['login_id'];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '';
$admin_filter = isset($_GET['admin_filter']) ? $_GET['admin_filter'] : '';

// Prepare filter conditions
$where_clause = "";
$count_where_clause = "";
$params = [];
$count_params = [];
$types = "";
$count_types = "";

// Date range filter
if (!empty($date_range)) {
    $dates = explode(' - ', $date_range);
    if (count($dates) === 2) {
        $start_date = date('Y-m-d 00:00:00', strtotime($dates[0]));
        $end_date = date('Y-m-d 23:59:59', strtotime($dates[1]));
        $where_clause .= " WHERE l.timestamp BETWEEN ? AND ?";
        $count_where_clause .= " WHERE timestamp BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $count_params[] = $start_date;
        $count_params[] = $end_date;
        $types .= "ss";
        $count_types .= "ss";
    }
}

// Activity type filter
if (!empty($filter) && $filter !== 'all') {
    if ($filter === 'login') {
        $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
        $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
        $where_clause .= "(l.activity LIKE ? OR l.activity LIKE ?)";
        $count_where_clause .= "(activity LIKE ? OR activity LIKE ?)";
        $params[] = '%login%';
        $params[] = '%logout%';
        $count_params[] = '%login%';
        $count_params[] = '%logout%';
        $types .= "ss";
        $count_types .= "ss";
    } elseif ($filter === 'security') {
        $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
        $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
        $where_clause .= "(l.activity LIKE ? OR l.activity LIKE ? OR l.activity LIKE ?)";
        $count_where_clause .= "(activity LIKE ? OR activity LIKE ? OR activity LIKE ?)";
        $params[] = '%password%';
        $params[] = '%security%';
        $params[] = '%2fa%';
        $count_params[] = '%password%';
        $count_params[] = '%security%';
        $count_params[] = '%2fa%';
        $types .= "sss";
        $count_types .= "sss";
    } elseif ($filter === 'election') {
        $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
        $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
        $where_clause .= "(l.activity LIKE ? OR l.activity LIKE ? OR l.activity LIKE ?)";
        $count_where_clause .= "(activity LIKE ? OR activity LIKE ? OR activity LIKE ?)";
        $params[] = '%election%';
        $params[] = '%vote%';
        $params[] = '%ballot%';
        $count_params[] = '%election%';
        $count_params[] = '%vote%';
        $count_params[] = '%ballot%';
        $types .= "sss";
        $count_types .= "sss";
    } elseif ($filter === 'user') {
        $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
        $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
        $where_clause .= "(l.activity LIKE ? OR l.activity LIKE ? OR l.activity LIKE ?)";
        $count_where_clause .= "(activity LIKE ? OR activity LIKE ? OR activity LIKE ?)";
        $params[] = '%user%';
        $params[] = '%voter%';
        $params[] = '%admin%';
        $count_params[] = '%user%';
        $count_params[] = '%voter%';
        $count_params[] = '%admin%';
        $types .= "sss";
        $count_types .= "sss";
    } elseif ($filter === 'self') {
        $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
        $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
        $where_clause .= "l.adminID = ?";
        $count_where_clause .= "adminID = ?";
        $params[] = $admin_id;
        $count_params[] = $admin_id;
        $types .= "i";
        $count_types .= "i";
    }
}

// Admin filter
if (!empty($admin_filter) && is_numeric($admin_filter)) {
    $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
    $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
    $where_clause .= "l.adminID = ?";
    $count_where_clause .= "adminID = ?";
    $params[] = $admin_filter;
    $count_params[] = $admin_filter;
    $types .= "i";
    $count_types .= "i";
}

// Add search condition if provided
if (!empty($search)) {
    $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
    $count_where_clause = empty($count_where_clause) ? " WHERE " : $count_where_clause . " AND ";
    $where_clause .= "(l.activity LIKE ? OR l.ip_address LIKE ? OR a.name LIKE ?)";
    $count_where_clause .= "(activity LIKE ? OR ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
    $types .= "sss";
    $count_types .= "ss";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM admin_activity_log" . $count_where_clause;
$count_stmt = $conn->prepare($count_sql);

if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch activity logs with pagination
$log_sql = "SELECT l.*, a.name as admin_name, a.email as admin_email 
            FROM admin_activity_log l
            LEFT JOIN admins a ON l.adminID = a.adminID" . 
            $where_clause . 
            " ORDER BY l.timestamp DESC LIMIT ?, ?";

$log_stmt = $conn->prepare($log_sql);

// Add pagination parameters
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

if (!empty($params)) {
    $log_stmt->bind_param($types, ...$params);
}

$log_stmt->execute();
$activity_logs = $log_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get list of admins for filter dropdown
$admins_sql = "SELECT adminID, name, email FROM admins ORDER BY name";
$admins_result = $conn->query($admins_sql);
$admins_list = $admins_result->fetch_all(MYSQLI_ASSOC);

// Clear logs function
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['clear_logs'])) {
        // Archive logs before deleting (optional)
        $archive_sql = "INSERT INTO admin_activity_log_archive 
                        SELECT * FROM admin_activity_log 
                        WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->execute();
        
        // Delete logs older than 30 days
        $clear_sql = "DELETE FROM admin_activity_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $clear_stmt = $conn->prepare($clear_sql);
        
        if ($clear_stmt->execute()) {
            $_SESSION['success_message'] = "Old activity logs cleared successfully.";
            
            // Log this activity
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (adminID, activity, ip_address) VALUES (?, ?, ?)");
            $activity = "Cleared old activity logs (older than 30 days)";
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("iss", $admin_id, $activity, $ip);
            $log_stmt->execute();
            
            // Refresh the page to show updated logs
            header("Location: activity.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to clear logs: " . $conn->error;
        }
    }
    
    // Export functionality
    if (isset($_POST['export_logs'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, ['Timestamp', 'Admin Name', 'Admin Email', 'Activity', 'IP Address', 'User Agent']);
        
        // Get all logs with current filters
        $export_sql = "SELECT l.*, a.name as admin_name, a.email as admin_email 
                      FROM admin_activity_log l
                      LEFT JOIN admins a ON l.adminID = a.adminID" . 
                      $where_clause . 
                      " ORDER BY l.timestamp DESC";
        
        $export_stmt = $conn->prepare($export_sql);
        
        if (!empty($params)) {
            // Remove pagination parameters for export
            $export_params = $params;
            $export_types = $types;
            
            if (count($export_params) >= 2) {
                array_pop($export_params);
                array_pop($export_params);
                $export_types = substr($export_types, 0, -2);
            }
            
            if (!empty($export_types)) {
                $export_stmt->bind_param($export_types, ...$export_params);
            }
        }
        
        $export_stmt->execute();
        $export_result = $export_stmt->get_result();
        
        while ($row = $export_result->fetch_assoc()) {
            fputcsv($output, [
                $row['timestamp'],
                $row['admin_name'],
                $row['admin_email'],
                $row['activity'],
                $row['ip_address'],
                $row['user_agent'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit();
    }
}

// Page title
$page_title = "Activity Log";
include 'includes/header.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Admin Panel</title>
    
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Date Range Picker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
    <!-- Custom CSS -->
    <style>
    :root {
        --primary-color: #4e73df;
        --primary-light: #e8f1ff;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-400: #ced4da;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --gray-900: #212529;
        --border-radius: 0.375rem;
        --box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }

    body {
        background-color: #f5f7fb;
        color: #4a4a4a;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 1.5rem;
        background-color: white;
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid var(--gray-200);
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: var(--gray-800);
    }

    .table-responsive {
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
        font-size: 0.875rem;
        color: var(--gray-700);
    }

    .table thead th {
        border-bottom-width: 1px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: var(--gray-600);
        background-color: var(--gray-100);
        padding: 0.75rem 1rem;
        border-top: none;
    }

    .table tbody tr {
        transition: all 0.15s ease;
        background-color: white;
    }

    .table tbody tr:hover {
        background-color: var(--primary-light);
    }

    .table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-top: 1px solid var(--gray-200);
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        letter-spacing: 0.5px;
    }

    .activity-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .activity-login {
        background-color: var(--primary-color);
    }

    .activity-security {
        background-color: var(--danger-color);
    }

    .activity-election {
        background-color: var(--success-color);
    }

    .activity-user {
        background-color: var(--warning-color);
    }

    .activity-default {
        background-color: var(--secondary-color);
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: #3d63d6;
        border-color: #3d63d6;
    }

    .btn-outline-secondary {
        color: var(--secondary-color);
        border-color: var(--gray-400);
    }

    .btn-outline-secondary:hover {
        background-color: var(--gray-200);
        border-color: var(--gray-400);
        color: var(--gray-700);
    }

    .btn-outline-danger {
        color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .btn-outline-danger:hover {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
    }

    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .page-link {
        color: var(--primary-color);
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--gray-300);
    }

    .page-link:hover {
        color: var(--primary-color);
        background-color: var(--gray-200);
        border-color: var(--gray-300);
    }

    .form-control, .form-select {
        border: 1px solid var(--gray-300);
        padding: 0.375rem 0.75rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .input-group-text {
        background-color: var(--gray-100);
        border: 1px solid var(--gray-300);
        color: var(--gray-600);
    }

    .alert {
        border: none;
        border-left: 4px solid transparent;
    }

    .alert-success {
        background-color: rgba(40, 167, 69, 0.1);
        border-left-color: var(--success-color);
        color: #155724;
    }

    .alert-danger {
        background-color: rgba(220, 53, 69, 0.1);
        border-left-color: var(--danger-color);
        color: #721c24;
    }

    .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-left-color: var(--warning-color);
        color: #856404;
    }

    .alert-info {
        background-color: rgba(23, 162, 184, 0.1);
        border-left-color: var(--info-color);
        color: #0c5460;
    }

    /* Filter chips */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        background-color: var(--gray-200);
        border-radius: 50px;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }

    .filter-chip .close {
        font-size: 0.875rem;
        margin-left: 0.5rem;
        opacity: 0.7;
    }

    .filter-chip .close:hover {
        opacity: 1;
    }

    /* Empty state */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        background-color: white;
        border-radius: var(--border-radius);
    }

    .empty-state i {
        font-size: 2.5rem;
        color: var(--gray-400);
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-500);
        margin-bottom: 0;
    }

    /* Timeline view */
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: var(--gray-200);
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: var(--primary-color);
        border: 2px solid white;
        z-index: 1;
    }

    .timeline-card {
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        background-color: white;
        transition: all 0.2s ease;
    }

    .timeline-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 0.125rem 0.5rem rgba(78, 115, 223, 0.1);
    }

    /* Stats cards */
    .stat-card {
        border-left: 4px solid transparent;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }

    .stat-card-primary {
        border-left-color: var(--primary-color);
    }

    .stat-card-success {
        border-left-color: var(--success-color);
    }

    .stat-card-warning {
        border-left-color: var(--warning-color);
    }

    .stat-card-danger {
        border-left-color: var(--danger-color);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
        }
        
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
        }
        
        .table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--gray-600);
            margin-right: 1rem;
            width: 40%;
        }
        
        .table tbody td:last-child {
            border-bottom: none;
        }
        
        .filter-section .col-md-6 {
            margin-bottom: 1rem;
        }
    }

    /* Loading spinner */
    .loading-spinner {
        display: none;
        width: 2rem;
        height: 2rem;
        border: 0.25em solid rgba(78, 115, 223, 0.2);
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Modal customizations */
    .modal-content {
        border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid var(--gray-200);
        background-color: white;
    }

    .modal-footer {
        border-top: 1px solid var(--gray-200);
        background-color: var(--gray-100);
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--gray-100);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #3d63d6;
    }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
         
        
        <!-- Main Content -->
        <main class="col-md-10 ms-auto col-lg-9 px-md-6 py-3"><br><br><br>
            <div class="container-fluid">
                <!-- Header Section -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-6 pb-2 mb-2 border-bottom">
                    <h1 class="h2 text-gray-800">
                        <i class="fas fa-history me-2"></i>
                        Activity Log
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="fas fa-download me-1"></i>
                                Export
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                                <i class="fas fa-trash me-1"></i>
                                Clear Logs
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Display messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Search and Filter Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </span>
                                        <input type="text" name="search" class="form-control" placeholder="Search activities..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="filter" class="form-select">
                                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Activities</option>
                                        <option value="login" <?php echo $filter === 'login' ? 'selected' : ''; ?>>Login Events</option>
                                        <option value="security" <?php echo $filter === 'security' ? 'selected' : ''; ?>>Security Events</option>
                                        <option value="election" <?php echo $filter === 'election' ? 'selected' : ''; ?>>Election Events</option>
                                        <option value="user" <?php echo $filter === 'user' ? 'selected' : ''; ?>>User Management</option>
                                        <option value="self" <?php echo $filter === 'self' ? 'selected' : ''; ?>>My Activities</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="admin_filter" class="form-select">
                                        <option value="">All Admins</option>
                                        <?php foreach ($admins_list as $admin): ?>
                                            <option value="<?php echo $admin['adminID']; ?>" <?php echo $admin_filter == $admin['adminID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($admin['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="date_range" class="form-control" id="dateRangePicker" placeholder="Date range" value="<?php echo htmlspecialchars($date_range); ?>">
                                </div>
                            </div>
                            
                            <!-- Active filters display -->
                            <?php if ($filter !== 'all' || !empty($search) || !empty($date_range) || !empty($admin_filter)): ?>
                            <div class="mt-3">
                                <h6 class="text-muted small mb-2">Active Filters:</h6>
                                <div class="d-flex flex-wrap">
                                    <?php if ($filter !== 'all'): ?>
                                    <span class="filter-chip">
                                        <?php echo ucfirst($filter); ?> events
                                        <a href="<?php echo remove_query_param('filter'); ?>" class="close text-decoration-none">&times;</a>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($search)): ?>
                                    <span class="filter-chip">
                                        Search: "<?php echo htmlspecialchars($search); ?>"
                                        <a href="<?php echo remove_query_param('search'); ?>" class="close text-decoration-none">&times;</a>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($date_range)): ?>
                                    <span class="filter-chip">
                                        Date: <?php echo htmlspecialchars($date_range); ?>
                                        <a href="<?php echo remove_query_param('date_range'); ?>" class="close text-decoration-none">&times;</a>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($admin_filter)): 
                                        $selected_admin = array_filter($admins_list, function($a) use ($admin_filter) {
                                            return $a['adminID'] == $admin_filter;
                                        });
                                        $selected_admin = reset($selected_admin);
                                    ?>
                                    <span class="filter-chip">
                                        Admin: <?php echo htmlspecialchars($selected_admin['name']); ?>
                                        <a href="<?php echo remove_query_param('admin_filter'); ?>" class="close text-decoration-none">&times;</a>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-sm btn-link text-danger ms-auto">
                                        Clear all
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-start border-primary border-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-muted small mb-1">Total Activities</h6>
                                        <h4 class="mb-0"><?php echo number_format($total_rows); ?></h4>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-history text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-start border-success border-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-muted small mb-1">Today's Activities</h6>
                                        <h4 class="mb-0">
                                            <?php 
                                            $today_sql = "SELECT COUNT(*) as count FROM admin_activity_log WHERE DATE(timestamp) = CURDATE()";
                                            $today_result = $conn->query($today_sql);
                                            echo number_format($today_result->fetch_assoc()['count']);
                                            ?>
                                        </h4>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-calendar-day text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-start border-warning border-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-muted small mb-1">Your Activities</h6>
                                        <h4 class="mb-0">
                                            <?php 
                                            $user_sql = "SELECT COUNT(*) as count FROM admin_activity_log WHERE adminID = ?";
                                            $user_stmt = $conn->prepare($user_sql);
                                            $user_stmt->bind_param("i", $admin_id);
                                            $user_stmt->execute();
                                            echo number_format($user_stmt->get_result()->fetch_assoc()['count']);
                                            ?>
                                        </h4>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-user text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-start border-danger border-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-muted small mb-1">Security Events</h6>
                                        <h4 class="mb-0">
                                            <?php 
                                            $security_sql = "SELECT COUNT(*) as count FROM admin_activity_log WHERE activity LIKE '%password%' OR activity LIKE '%security%' OR activity LIKE '%2fa%'";
                                            $security_result = $conn->query($security_sql);
                                            echo number_format($security_result->fetch_assoc()['count']);
                                            ?>
                                        </h4>
                                    </div>
                                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-shield-alt text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- View Toggle -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Activity Records</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" id="tableViewBtn">
                            <i class="fas fa-table"></i> Table
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="timelineViewBtn">
                            <i class="fas fa-stream"></i> Timeline
                        </button>
                    </div>
                </div>
                
                <!-- Activity Log Table View -->
                <div id="tableView" class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="180">Timestamp</th>
                                        <th scope="col">Admin</th>
                                        <th scope="col">Activity</th>
                                        <th scope="col" width="140">IP Address</th>
                                        <th scope="col" width="100">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($activity_logs)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-info-circle"></i>
                                                <h5>No activity logs found</h5>
                                                <p class="mb-0">Try adjusting your search or filter criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($activity_logs as $log): ?>
                                        <tr>
                                            <td data-label="Timestamp">
                                                <div class="d-flex align-items-center">
                                                    <span class="activity-indicator <?php echo getActivityClass($log['activity']); ?>"></span>
                                                    <span><?php echo date('M j, Y g:i a', strtotime($log['timestamp'])); ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Admin">
                                                <?php if ($log['adminID'] == $admin_id): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">You</span>
                                                <?php else: ?>
                                                <?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Activity">
                                                <?php echo htmlspecialchars($log['activity']); ?>
                                                <span class="badge <?php echo getActivityBadgeClass($log['activity']); ?> float-end">
                                                    <?php echo getActivityType($log['activity']); ?>
                                                </span>
                                            </td>
                                            <td data-label="IP Address">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    <?php echo htmlspecialchars($log['ip_address']); ?>
                                                </span>
                                            </td>
                                            <td data-label="Details">
                                                <button type="button" class="btn btn-sm btn-outline-primary view-details" 
                                                        data-bs-toggle="modal" data-bs-target="#activityDetailModal"
                                                        data-activity-id="<?php echo $log['id']; ?>"
                                                        data-timestamp="<?php echo date('M j, Y g:i:s a', strtotime($log['timestamp'])); ?>"
                                                        data-admin="<?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>"
                                                        data-admin-email="<?php echo htmlspecialchars($log['admin_email'] ?? 'N/A'); ?>"
                                                        data-activity="<?php echo htmlspecialchars($log['activity']); ?>"
                                                        data-ip="<?php echo htmlspecialchars($log['ip_address']); ?>"
                                                        data-user-agent="<?php echo htmlspecialchars($log['user_agent'] ?? 'N/A'); ?>">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Log Timeline View -->
                <div id="timelineView" class="card shadow-sm mb-4" style="display: none;">
                    <div class="card-body">
                        <?php if (empty($activity_logs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-info-circle"></i>
                                <h5>No activity logs found</h5>
                                <p class="mb-0">Try adjusting your search or filter criteria</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php 
                                $current_date = '';
                                foreach ($activity_logs as $log): 
                                    $log_date = date('F j, Y', strtotime($log['timestamp']));
                                    if ($log_date !== $current_date):
                                        $current_date = $log_date;
                                ?>
                                <div class="mb-3">
                                    <h6 class="text-muted"><?php echo $current_date; ?></h6>
                                </div>
                                <?php endif; ?>
                                <div class="timeline-item mb-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <span class="activity-indicator <?php echo getActivityClass($log['activity']); ?>"></span>
                                                        <?php echo htmlspecialchars($log['activity']); ?>
                                                    </h6>
                                                    <p class="small text-muted mb-1">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo date('g:i a', strtotime($log['timestamp'])); ?>
                                                    </p>
                                                    <p class="small mb-0">
                                                        <?php if ($log['adminID'] == $admin_id): ?>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary">You</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                            <?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>
                                                        </span>
                                                        <?php endif; ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">
                                                            <?php echo htmlspecialchars($log['ip_address']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary view-details" 
                                                        data-bs-toggle="modal" data-bs-target="#activityDetailModal"
                                                        data-activity-id="<?php echo $log['id']; ?>"
                                                        data-timestamp="<?php echo date('M j, Y g:i:s a', strtotime($log['timestamp'])); ?>"
                                                        data-admin="<?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>"
                                                        data-admin-email="<?php echo htmlspecialchars($log['admin_email'] ?? 'N/A'); ?>"
                                                        data-activity="<?php echo htmlspecialchars($log['activity']); ?>"
                                                        data-ip="<?php echo htmlspecialchars($log['ip_address']); ?>"
                                                        data-user-agent="<?php echo htmlspecialchars($log['user_agent'] ?? 'N/A'); ?>">
                                                    <i class="fas fa-eye me-1"></i> Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Activity log pagination" class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing <?php echo min(($page - 1) * $limit + 1, $total_rows); ?>-<?php echo min($page * $limit, $total_rows); ?> of <?php echo number_format($total_rows); ?> activities
                    </div>
                    
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo build_pagination_url(1); ?>" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo build_pagination_url($page - 1); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        // Always show first page
                        if ($page > 3): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo build_pagination_url(1); ?>">1</a>
                        </li>
                        <?php if ($page > 4): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php
                        // Show pages around current page
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo build_pagination_url($i); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php
                        // Always show last page
                        if ($page < $total_pages - 2): ?>
                        <?php if ($page < $total_pages - 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo build_pagination_url($total_pages); ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo build_pagination_url($page + 1); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo build_pagination_url($total_pages); ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Activity Detail Modal -->
<div class="modal fade" id="activityDetailModal" tabindex="-1" aria-labelledby="activityDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="activityDetailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    Activity Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-600">Timestamp</label>
                            <p id="modal-timestamp" class="mb-1">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-600">Activity Type</label>
                            <p id="modal-activity-type" class="mb-1">-</p>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-600">Admin</label>
                            <p id="modal-admin" class="mb-1">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-600">Admin Email</label>
                            <p id="modal-admin-email" class="mb-1">-</p>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-gray-600">Activity</label>
                    <p id="modal-activity" class="mb-1">-</p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-600">IP Address</label>
                            <p id="modal-ip" class="mb-1">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-600">User Agent</label>
                            <p id="modal-user-agent" class="mb-1 small text-muted">-</p>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-gray-600">Additional Info</label>
                    <div class="bg-light p-3 rounded small">
                        <pre id="modal-additional-info" class="mb-0">No additional information available</pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Confirmation Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="clearLogsModalLabel">
                    <i class="fas fa-trash me-2"></i>
                    Clear Activity Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action will permanently delete all activity logs older than 30 days.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Number of logs to be deleted:</label>
                        <div class="alert alert-danger">
                            <?php 
                            $old_logs_sql = "SELECT COUNT(*) as count FROM admin_activity_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)";
                            $old_logs_result = $conn->query($old_logs_sql);
                            echo number_format($old_logs_result->fetch_assoc()['count']);
                            ?> records
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                        <label class="form-check-label" for="confirmDelete">
                            I understand this action cannot be undone
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="clear_logs" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Clear Logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-download me-2"></i>
                    Export Activity Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Export Format</label>
                        <select class="form-select" name="export_format">
                            <option value="csv">CSV (Comma Separated Values)</option>
                            <option value="json">JSON (JavaScript Object Notation)</option>
                            <option value="excel">Excel (XLSX)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date Range</label>
                        <select class="form-select" name="export_date_range">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customDateRange" style="display: none;">
                        <label class="form-label fw-bold">Custom Date Range</label>
                        <input type="text" class="form-control" name="export_custom_date_range" id="exportDateRangePicker" placeholder="Select date range">
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The export will include all currently filtered results.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="export_logs" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date range picker
    $('#dateRangePicker').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'MMM D, YYYY'
        }
    });

    $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY'));
        $('#filterForm').submit();
    });

    $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#filterForm').submit();
    });

    // Initialize export date range picker
    $('#exportDateRangePicker').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'MMM D, YYYY'
        }
    });

    $('#exportDateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY'));
    });

    // Show/hide custom date range field
    $('select[name="export_date_range"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });

    // View details modal
    const viewButtons = document.querySelectorAll('.view-details');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const timestamp = this.getAttribute('data-timestamp');
            const admin = this.getAttribute('data-admin');
            const adminEmail = this.getAttribute('data-admin-email');
            const activity = this.getAttribute('data-activity');
            const ip = this.getAttribute('data-ip');
            const userAgent = this.getAttribute('data-user-agent');
            
            document.getElementById('modal-timestamp').textContent = timestamp;
            document.getElementById('modal-admin').textContent = admin;
            document.getElementById('modal-admin-email').textContent = adminEmail;
            document.getElementById('modal-activity').textContent = activity;
            document.getElementById('modal-ip').textContent = ip;
            document.getElementById('modal-user-agent').textContent = userAgent;
            
            // Set activity type
            const activityType = getActivityTypeFromText(activity);
            document.getElementById('modal-activity-type').textContent = activityType;
            
            // Set additional info (could be enhanced with more data)
            const additionalInfo = {
                'Activity Type': activityType,
                'IP Location': 'Not determined',
                'Browser': getBrowserFromUserAgent(userAgent),
                'OS': getOSFromUserAgent(userAgent)
            };
            
            document.getElementById('modal-additional-info').textContent = 
                JSON.stringify(additionalInfo, null, 2);
        });
    });
    
    // View toggle
    const tableViewBtn = document.getElementById('tableViewBtn');
    const timelineViewBtn = document.getElementById('timelineViewBtn');
    const tableView = document.getElementById('tableView');
    const timelineView = document.getElementById('timelineView');
    
    tableViewBtn.addEventListener('click', function() {
        tableView.style.display = 'block';
        timelineView.style.display = 'none';
        tableViewBtn.classList.add('active');
        timelineViewBtn.classList.remove('active');
    });
    
    timelineViewBtn.addEventListener('click', function() {
        tableView.style.display = 'none';
        timelineView.style.display = 'block';
        tableViewBtn.classList.remove('active');
        timelineViewBtn.classList.add('active');
    });
    
    // Auto-refresh every 5 minutes (optional)
    setInterval(function() {
        // Only refresh if not on a modal and not filtering
        if (!document.querySelector('.modal.show') && 
            window.location.search === '' || window.location.search === '?page=1') {
            window.location.reload();
        }
    }, 300000); // 5 minutes
    
    // Helper functions for user agent parsing
    function getBrowserFromUserAgent(ua) {
        if (ua.includes('Firefox')) return 'Firefox';
        if (ua.includes('Chrome')) return 'Chrome';
        if (ua.includes('Safari')) return 'Safari';
        if (ua.includes('Edge')) return 'Edge';
        if (ua.includes('Opera')) return 'Opera';
        if (ua.includes('MSIE') || ua.includes('Trident/')) return 'Internet Explorer';
        return 'Unknown';
    }
    
    function getOSFromUserAgent(ua) {
        if (ua.includes('Windows')) return 'Windows';
        if (ua.includes('Macintosh')) return 'Mac OS';
        if (ua.includes('Linux')) return 'Linux';
        if (ua.includes('Android')) return 'Android';
        if (ua.includes('iOS')) return 'iOS';
        return 'Unknown';
    }
    
    function getActivityTypeFromText(activity) {
        activity = activity.toLowerCase();
        
        if (activity.includes('login') || activity.includes('logout')) {
            return 'Login Event';
        } else if (activity.includes('password') || activity.includes('security') || activity.includes('2fa')) {
            return 'Security Event';
        } else if (activity.includes('election') || activity.includes('vote') || activity.includes('ballot')) {
            return 'Election Event';
        } else if (activity.includes('user') || activity.includes('voter') || activity.includes('admin')) {
            return 'User Management';
        } else {
            return 'General Activity';
        }
    }
});
</script>

<?php
// Helper functions
function getActivityClass($activity) {
    $activity = strtolower($activity);
    
    if (strpos($activity, 'login') !== false || strpos($activity, 'logout') !== false) {
        return 'activity-login';
    } elseif (strpos($activity, 'password') !== false || strpos($activity, 'security') !== false || strpos($activity, '2fa') !== false) {
        return 'activity-security';
    } elseif (strpos($activity, 'election') !== false || strpos($activity, 'vote') !== false || strpos($activity, 'ballot') !== false) {
        return 'activity-election';
    } elseif (strpos($activity, 'user') !== false || strpos($activity, 'voter') !== false || strpos($activity, 'admin') !== false) {
        return 'activity-user';
    } else {
        return 'activity-default';
    }
}

function getActivityBadgeClass($activity) {
    $activity = strtolower($activity);
    
    if (strpos($activity, 'login') !== false || strpos($activity, 'logout') !== false) {
        return 'badge-login';
    } elseif (strpos($activity, 'password') !== false || strpos($activity, 'security') !== false || strpos($activity, '2fa') !== false) {
        return 'badge-security';
    } elseif (strpos($activity, 'election') !== false || strpos($activity, 'vote') !== false || strpos($activity, 'ballot') !== false) {
        return 'badge-election';
    } elseif (strpos($activity, 'user') !== false || strpos($activity, 'voter') !== false || strpos($activity, 'admin') !== false) {
        return 'badge-user';
    } else {
        return 'badge bg-secondary bg-opacity-10 text-secondary';
    }
}

function getActivityType($activity) {
    $activity = strtolower($activity);
    
    if (strpos($activity, 'login') !== false || strpos($activity, 'logout') !== false) {
        return 'Login';
    } elseif (strpos($activity, 'password') !== false || strpos($activity, 'security') !== false || strpos($activity, '2fa') !== false) {
        return 'Security';
    } elseif (strpos($activity, 'election') !== false || strpos($activity, 'vote') !== false || strpos($activity, 'ballot') !== false) {
        return 'Election';
    } elseif (strpos($activity, 'user') !== false || strpos($activity, 'voter') !== false || strpos($activity, 'admin') !== false) {
        return 'User';
    } else {
        return 'General';
    }
}

function build_pagination_url($page) {
    $url = htmlspecialchars($_SERVER["PHP_SELF"]) . "?page=" . $page;
    
    if (!empty($_GET['filter']) && $_GET['filter'] !== 'all') {
        $url .= "&filter=" . $_GET['filter'];
    }
    
    if (!empty($_GET['search'])) {
        $url .= "&search=" . urlencode($_GET['search']);
    }
    
    if (!empty($_GET['date_range'])) {
        $url .= "&date_range=" . urlencode($_GET['date_range']);
    }
    
    if (!empty($_GET['admin_filter'])) {
        $url .= "&admin_filter=" . $_GET['admin_filter'];
    }
    
    return $url;
}

function remove_query_param($param) {
    $query = $_GET;
    unset($query[$param]);
    return htmlspecialchars($_SERVER["PHP_SELF"]) . '?' . http_build_query($query);
}
?>