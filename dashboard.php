<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';
require_once 'update_election_status.php'; // Include the status updater

// Automatically update election statuses when dashboard is loaded
$statusUpdateResult = updateElectionStatuses();
if (!$statusUpdateResult['success']) {
    error_log("Dashboard: Failed to update election statuses: " . implode(", ", $statusUpdateResult['errors']));
}

error_reporting(E_ALL);

ini_set('display_errors', 1);

// Session start is removed as it's already in auth_check.php
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

// Initialize dashboard stats if not set
if (!isset($_SESSION['dashboard_stats'])) {
    $_SESSION['dashboard_stats'] = [];
}

// Get fresh count of categories
$categoriesQuery = $conn->prepare("SELECT COUNT(*) as total FROM categories");
$categoriesQuery->execute();
$categoriesCount = $categoriesQuery->get_result()->fetch_assoc()['total'];

// Update the session variable
$_SESSION['dashboard_stats']['total_active_categories'] = $categoriesCount;

// Initialize variables
$dashboard_stats = [
    'total_elections' => 0,
    'total_active_categories' => 0,
    'total_voters' => 0,
    'total_voted' => 0,
    'participation_percentage' => 0,
    'election_title' => 'No Active Election',
    'election_id' => null
];

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $tables = ['elections', 'categories', 'students', 'votes'];
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$check || $check->num_rows == 0) {
            throw new Exception("Table '$table' doesn't exist");
        }
    }

    // Corrected query for participation rate
    $query = "
    SELECT 
        (SELECT COUNT(*) FROM elections) AS total_elections,
        (SELECT COUNT(*) FROM categories WHERE electionID = e.electionID) AS total_active_categories,
        (SELECT COUNT(*) FROM students WHERE status = 'Active') AS total_voters,
        (SELECT COUNT(DISTINCT studentID) FROM votes v WHERE v.electionID = e.electionID) AS total_voted,
        e.name AS election_title,
        e.electionID AS election_id,
        e.status
    FROM elections e
    WHERE e.status = 'Ongoing'
    LIMIT 1";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dashboard_stats = [
            'total_elections' => $row["total_elections"] ?? 0,
            'total_active_categories' => $row["total_active_categories"] ?? 0,
            'total_voters' => $row["total_voters"] ?? 0,
            'total_voted' => $row["total_voted"] ?? 0,
            'participation_percentage' => ($row["total_voters"] > 0) ? round(($row["total_voted"] / $row["total_voters"]) * 100) : 0,
            'election_title' => $row["election_title"] ?? 'No Active Election',
            'election_id' => $row["election_id"] ?? null
        ];
    }
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error_message = "Error loading dashboard data: " . $e->getMessage();
}

// Fetch students data
$students = [];
try {
    $students_query = $conn->prepare("SELECT studentID, name, email, department, status, role as type, profilePicture FROM students");
    $students_query->execute();
    $students_result = $students_query->get_result();
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Students query error: " . $e->getMessage());
    $error_message = "Error loading student data: " . $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - SmartVote</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Theme Variables - Light Mode (Default) */
        :root {
            /* Light mode variables */
            --body-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --highlight-bg: #f8f9fa;
            --shadow-color: rgba(0,0,0,0.05);
            --icon-color: #0d6efd;
            --header-bg: #ffffff;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --primary-color: #0d6efd;
            
            /* Default Bootstrap theme for light mode */
            color-scheme: light;
        }
        
        /* Dark Mode Variables */
        [data-bs-theme="dark"] {
            --body-bg: #212529;
            --card-bg: #2b3035;
            --text-color: #f8f9fa;
            --text-muted: #adb5bd;
            --border-color: #495057;
            --highlight-bg: #343a40;
            --shadow-color: rgba(0,0,0,0.2);
            --icon-color: #6ea8fe;
            --header-bg: #343a40;
            --success-color: #75b798;
            --warning-color: #ffda6a;
            --danger-color: #ea868f;
            --info-color: #6edff6;
            --primary-color: #6ea8fe;
            
            /* Default Bootstrap theme for dark mode */
            color-scheme: dark;
        }
        
        /* Global styles */
        body {
            background-color: var(--body-bg);
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            color: var(--text-color);
        }
        
        .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }
        
        .bg-white {
            background-color: var(--card-bg) !important;
        }
        
        .border-bottom {
            border-color: var(--border-color) !important;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        /* Light/Dark mode specific colors for card icons */
        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.15);
            color: var(--primary-color);
        }
        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.15);
            color: var(--success-color);
        }
        .bg-info-light {
            background-color: rgba(13, 202, 240, 0.15);
            color: var(--info-color);
        }
        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.15);
            color: var(--warning-color);
        }
        
        .progress-thin {
            height: 5px;
        }
        .search-box {
            position: relative;
        }
        .search-box:before {
            content: "\F52A";
            font-family: bootstrap-icons;
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 10;
        }
        .search-box input {
            padding-left: 30px;
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--border-color);
        }
        
        /* Dark mode override for form controls */
        [data-bs-theme="dark"] .form-control {
            background-color: var(--highlight-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }
        
        [data-bs-theme="dark"] .input-group-text {
            background-color: var(--highlight-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .initials-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--highlight-bg);
            font-weight: bold;
            color: var(--text-muted);
        }
        
        /* Table styles for dark mode */
        [data-bs-theme="dark"] .table {
            color: var(--text-color);
        }
        
        [data-bs-theme="dark"] thead th {
            background-color: var(--highlight-bg);
            color: var(--text-color);
        }
        
        [data-bs-theme="dark"] .table-hover tbody tr:hover {
            background-color: var(--highlight-bg);
        }
        
        /* Share and Export Modal Styles */
        .share-btn {
            transition: all 0.3s ease;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .share-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        .share-btn i {
            transition: all 0.3s ease;
        }
        .share-btn:hover i {
            transform: scale(1.2);
        }
        .export-format-option {
            flex: 1;
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            background-color: var(--card-bg);
        }
        .export-format-option:hover {
            background-color: var(--highlight-bg);
            border-color: var(--primary-color);
        }
        .export-format-option input:checked + label {
            color: var(--primary-color);
            font-weight: 500;
        }
        .export-format-option input:checked + label i {
            color: var(--primary-color);
        }
        .list-group-item {
            transition: all 0.2s ease;
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--border-color);
        }
        .list-group-item:hover {
            background-color: var(--highlight-bg);
        }
        .form-switch .form-check-input {
            cursor: pointer;
        }
        .form-switch .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        /* Additional UI Improvements */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 0.5rem 1rem var(--shadow-color);
        }
        
        .card-body {
            position: relative;
            z-index: 1;
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        
        .table-hover tbody tr {
            transition: transform 0.2s ease, background-color 0.2s ease;
            border-radius: 8px;
        }
       
        /* Buttons styling */
        .btn {
            transition: all 0.3s ease;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-color);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Button gradients adjusted for dark mode */
        [data-bs-theme="light"] .btn-primary, 
        [data-bs-theme="light"] .btn-outline-primary:hover {
            background-image: linear-gradient(to right, #0d6efd, #0a58ca);
        }
        
        [data-bs-theme="light"] .btn-success, 
        [data-bs-theme="light"] .btn-outline-success:hover {
            background-image: linear-gradient(to right, #198754, #146c43);
        }
        
        [data-bs-theme="dark"] .btn-primary,
        [data-bs-theme="dark"] .btn-outline-primary:hover {
            background-image: linear-gradient(to right, #6ea8fe, #0d6efd);
        }
        
        [data-bs-theme="dark"] .btn-success,
        [data-bs-theme="dark"] .btn-outline-success:hover {
            background-image: linear-gradient(to right, #75b798, #198754);
        }
        
        .btn:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn:hover:after {
            left: 100%;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Dropdown styling for dark mode */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        [data-bs-theme="dark"] .dropdown-item {
            color: var(--text-color);
        }
        
        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: var(--highlight-bg);
        }
        
        /* Animated icons */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .card-icon i {
            animation: pulse 2s infinite;
        }
        
        /* Improved Progress Bar */
        .progress {
            overflow: visible;
            height: 10px;
            border-radius: 5px;
            background-color: var(--highlight-bg);
        }
        
        .progress-bar {
            position: relative;
            border-radius: 5px;
            overflow: visible;
        }
        
        [data-bs-theme="light"] .progress-bar {
            background-image: linear-gradient(to right, #0d6efd, #0a58ca);
        }
        
        [data-bs-theme="light"] .progress-bar.bg-success {
            background-image: linear-gradient(to right, #198754, #146c43);
        }
        
        [data-bs-theme="light"] .progress-bar.bg-warning {
            background-image: linear-gradient(to right, #ffc107, #e0a800);
        }
        
        [data-bs-theme="dark"] .progress-bar {
            background-image: linear-gradient(to right, #6ea8fe, #0d6efd);
        }
        
        [data-bs-theme="dark"] .progress-bar.bg-success {
            background-image: linear-gradient(to right, #75b798, #198754);
        }
        
        [data-bs-theme="dark"] .progress-bar.bg-warning {
            background-image: linear-gradient(to right, #ffda6a, #ffc107);
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            right: 0;
            top: -3px;
            height: 16px;
            width: 16px;
            border-radius: 50%;
            background-color: inherit;
            border: 2px solid var(--card-bg);
            box-shadow: 0 2px 5px var(--shadow-color);
        }
        
        /* Toast notifications */
        .toast {
            border-radius: 10px;
            box-shadow: 0 5px 15px var(--shadow-color);
            overflow: hidden;
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        /* Modal styling for dark mode */
        [data-bs-theme="dark"] .modal-content {
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--border-color);
        }
        
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            border-color: var(--border-color);
        }
        
        /* Tables */
        .table {
            box-shadow: 0 5px 15px var(--shadow-color);
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        thead th {
            background-color: var(--highlight-bg);
            border-bottom: 2px solid var(--border-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Alert styling for dark mode */
        [data-bs-theme="dark"] .alert {
            background-color: var(--highlight-bg);
            color: var(--text-color);
            border-color: var(--border-color);
        }
        
        /* Sidebar adjustments */
        .main-content {
            transition: all 0.3s ease;
        }
        
        .sidebar-footer {
            transition: all 0.3s ease;
        }
        
        /* Active Card Styling */
        .active-card {
            position: relative;
            overflow: hidden;
        }
        
        .active-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 5px;
        }
        
        .border-left-primary {
            border-left: 4px solid var(--primary-color) !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(var(--primary-color-rgb, 13, 110, 253), 0.15) !important;
        }
        
        .border-left-success {
            border-left: 4px solid var(--success-color) !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(var(--success-color-rgb, 25, 135, 84), 0.15) !important;
        }
        
        .border-left-info {
            border-left: 4px solid var(--info-color) !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(var(--info-color-rgb, 13, 202, 240), 0.15) !important;
        }
        
        .border-left-warning {
            border-left: 4px solid var(--warning-color) !important;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(var(--warning-color-rgb, 255, 193, 7), 0.15) !important;
        }
        
        .border-left-primary::before { background-color: var(--primary-color); }
        .border-left-success::before { background-color: var(--success-color); }
        .border-left-info::before { background-color: var(--info-color); }
        .border-left-warning::before { background-color: var(--warning-color); }
        
        .active-card .card-icon {
            animation: pulse 2s infinite;
        }
        
        .border-left-primary .card-icon { animation: pulse-primary 2s infinite; }
        .border-left-success .card-icon { animation: pulse-success 2s infinite; }
        .border-left-info .card-icon { animation: pulse-info 2s infinite; }
        .border-left-warning .card-icon { animation: pulse-warning 2s infinite; }
        
        /* Reduced color intensity for modals */
        .modal-header.bg-warning {
            background-color: #f0ad4e !important;
        }
        
        .modal-header.bg-success {
            background-color: #5cb85c !important;
        }
        
        .modal-header.bg-danger {
            background-color: #d9534f !important;
        }
        
        .modal-header.bg-info {
            background-color: #5bc0de !important;
        }
        
        /* Subdued button colors for modals */
        .btn-warning {
            background-color: #f0ad4e;
            border-color: #f0ad4e;
        }
        
        .btn-warning:hover {
            background-color: #ec971f;
            border-color: #ec971f;
        }
        
        .btn-success {
            background-color: #5cb85c;
            border-color: #5cb85c;
        }
        
        .btn-success:hover {
            background-color: #449d44;
            border-color: #449d44;
        }
        
        .btn-danger {
            background-color: #d9534f;
            border-color: #d9534f;
        }
        
        .btn-danger:hover {
            background-color: #c9302c;
            border-color: #c9302c;
        }
        
        /* Dark mode overrides for subdued colors */
        [data-bs-theme="dark"] .modal-header.bg-warning {
            background-color: #d4854b !important;
        }
        
        [data-bs-theme="dark"] .modal-header.bg-success {
            background-color: #4a9d4a !important;
        }
        
        [data-bs-theme="dark"] .modal-header.bg-danger {
            background-color: #c9302c !important;
        }
        
        [data-bs-theme="dark"] .modal-header.bg-info {
            background-color: #4a9fb8 !important;
        }
        
        [data-bs-theme="dark"] .btn-warning {
            background-color: #d4854b;
            border-color: #d4854b;
        }
        
        [data-bs-theme="dark"] .btn-success {
            background-color: #4a9d4a;
            border-color: #4a9d4a;
        }
        
        [data-bs-theme="dark"] .btn-danger {
            background-color: #c9302c;
            border-color: #c9302c;
        }
        
        /* Alert colors reduced intensity */
        .alert-info {
            background-color: #e8f4fd;
            color: #31708f;
            border-color: #bce8f1;
        }
        
        .alert-warning {
            background-color: #fcf8e3;
            color: #8a6d3b;
            border-color: #faebcc;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border-color: #d6e9c6;
        }
        
        [data-bs-theme="dark"] .alert-info {
            background-color: rgba(74, 159, 184, 0.15);
            color: #5bc0de;
            border-color: rgba(74, 159, 184, 0.3);
        }
        
        [data-bs-theme="dark"] .alert-warning {
            background-color: rgba(212, 133, 75, 0.15);
            color: #f0ad4e;
            border-color: rgba(212, 133, 75, 0.3);
        }
        
        [data-bs-theme="dark"] .alert-success {
            background-color: rgba(74, 157, 74, 0.15);
            color: #5cb85c;
            border-color: rgba(74, 157, 74, 0.3);
        }
        
        @keyframes pulse-primary {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); box-shadow: 0 0 10px rgba(var(--primary-color-rgb, 13, 110, 253), 0.3); }
            100% { transform: scale(1); }
        }
        
        @keyframes pulse-success {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); box-shadow: 0 0 10px rgba(var(--success-color-rgb, 25, 135, 84), 0.3); }
            100% { transform: scale(1); }
        }
        
        @keyframes pulse-info {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); box-shadow: 0 0 10px rgba(var(--info-color-rgb, 13, 202, 240), 0.3); }
            100% { transform: scale(1); }
        }
        
        @keyframes pulse-warning {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); box-shadow: 0 0 10px rgba(var(--warning-color-rgb, 255, 193, 7), 0.3); }
            100% { transform: scale(1); }
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
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="shareBtn"> <i class="bi bi-share action-icon icon"></i>&nbsp;Share</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="exportBtn"> <i class="bi bi-file-earmark-arrow-up profile-icon icon"></i>&nbsp;Export</button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                                <i class="bi bi-calendar"></i> This week
                            </button>
                        </div>
                    </div>
                    
                    <!-- Share Modal -->
                    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="shareModalLabel"><i class="bi bi-share-fill me-2"></i>Share Dashboard</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted mb-3 fw-light"><i class="bi bi-info-circle me-1"></i> Share this dashboard with others:</p>
                                    <div class="input-group mb-4">
                                        <input type="text" class="form-control form-control-lg" id="shareLink" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" readonly>
                                        <button class="btn btn-info" type="button" id="copyLinkBtn">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <h6 class="mb-3 text-center"><i class="bi bi-arrow-down-circle me-1"></i> Share via:</h6>
                                    <div class="d-flex justify-content-center gap-3">
                                        <button class="btn btn-outline-primary rounded-circle p-3 share-btn" id="shareEmailBtn" title="Email">
                                            <i class="bi bi-envelope-fill fs-4"></i>
                                        </button>
                                        <button class="btn btn-outline-success rounded-circle p-3 share-btn" id="shareWhatsappBtn" title="WhatsApp">
                                            <i class="bi bi-whatsapp fs-4"></i>
                                        </button>
                                        <button class="btn btn-outline-info rounded-circle p-3 share-btn" id="shareTelegramBtn" title="Telegram">
                                            <i class="bi bi-telegram fs-4"></i>
                                        </button>
                                        <button class="btn btn-outline-dark rounded-circle p-3 share-btn" id="shareTwitterBtn" title="Twitter">
                                            <i class="bi bi-twitter-x fs-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 justify-content-center">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Modal -->
                    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="exportModalLabel"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i>Export Dashboard Data</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted mb-4 fw-light"><i class="bi bi-info-circle me-1"></i> Choose the format and data to export:</p>
                                    <form id="exportForm">
                                        <div class="mb-4">
                                            <h6 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>Export Format</h6>
                                            <div class="d-flex gap-3">
                                                <div class="form-check export-format-option">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatCSV" value="csv" checked>
                                                    <label class="form-check-label d-flex flex-column align-items-center" for="formatCSV">
                                                        <i class="bi bi-file-earmark-spreadsheet fs-3 mb-2"></i>
                                                        <span>CSV</span>
                                                    </label>
                                                </div>
                                                <div class="form-check export-format-option">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel">
                                                    <label class="form-check-label d-flex flex-column align-items-center" for="formatExcel">
                                                        <i class="bi bi-file-earmark-excel fs-3 mb-2"></i>
                                                        <span>Excel</span>
                                                    </label>
                                                </div>
                                                <div class="form-check export-format-option">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatPDF" value="pdf">
                                                    <label class="form-check-label d-flex flex-column align-items-center" for="formatPDF">
                                                        <i class="bi bi-file-earmark-pdf fs-3 mb-2"></i>
                                                        <span>PDF</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <h6 class="mb-3"><i class="bi bi-database me-2"></i>Data to Export</h6>
                                            <div class="list-group">
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-graph-up me-2 text-primary"></i>
                                                        Dashboard Statistics
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="exportStats" checked>
                                                    </div>
                                                </label>
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-people me-2 text-success"></i>
                                                        Students List
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="exportStudents" checked>
                                                    </div>
                                                </label>
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-check2-square me-2 text-info"></i>
                                                        Elections Data
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="exportElections" checked>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer border-0 justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-success" id="exportSubmitBtn">
                                        <i class="bi bi-download me-2"></i>Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error Alert -->
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <!-- Elections Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100 <?php echo ($dashboard_stats['total_elections'] > 0) ? 'border-left-primary active-card' : ''; ?>">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"> <i class="bi bi-check2-circle profile-icon icon"></i>&nbsp;Elections</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-primary-light me-3">
                                            <i class="bi bi-box-seam-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo $dashboard_stats['total_elections']; ?></h2>
                                            <p class="text-muted mb-0"> <i class="bi bi-box-seam profile-icon icon"></i>&nbsp;Total Elections</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100 <?php echo ($dashboard_stats['total_active_categories'] > 0) ? 'border-left-success active-card' : ''; ?>">
                                <div class="card-body">
                                    <h5 class="card-title text-muted">  <i class="bi bi-list-task action-icon icon"></i>&nbsp;Categories</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-success-light me-3">
                                            <i class="bi bi-bookmark-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo $dashboard_stats['total_active_categories']; ?></h2>
                                            <p class="text-muted mb-0"> <i class="bi bi-grid-3x3-gap action-icon icon"></i>&nbsp;Active Categories</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Voters Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100 <?php echo ($dashboard_stats['total_voters'] > 0) ? 'border-left-info active-card' : ''; ?>">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"> <i class="bi bi-people department-icon icon"></i>&nbsp;Voters</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-info-light me-3">
                                            <i class="bi bi-people-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo $dashboard_stats['total_voters']; ?></h2>
                                            <p class="text-muted mb-0"> <i class="bi bi-person-check department-icon icon"></i>&nbsp;Registered Voters</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Participation Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100 <?php echo ($dashboard_stats['total_voted'] > 0) ? 'border-left-warning active-card' : ''; ?>">
                                <div class="card-body">
                                    <h5 class="card-title text-muted"><i class="bi bi-hand-thumbs-up role-icon icon"></i>&nbsp;Participation</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-warning-light me-3">
                                            <i class="bi bi-check2-circle fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0">
                                                <?php echo $dashboard_stats['total_voted']; ?>
                                                <small class="fs-6 text-<?php echo ($dashboard_stats['participation_percentage'] > 50) ? 'success' : 'danger'; ?>">
                                                    (<?php echo $dashboard_stats['participation_percentage']; ?>%)
                                                </small>
                                            </h2>
                                            <p class="text-muted mb-1"> <i class="bi bi-person-plus role-icon icon"></i>&nbsp;Votes Cast</p>
                                            <div class="progress progress-thin">
                                                <div class="progress-bar bg-<?php echo ($dashboard_stats['participation_percentage'] > 50) ? 'success' : 'warning'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $dashboard_stats['participation_percentage']; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Election Card -->
                    <?php if ($dashboard_stats['election_id']): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0"><i class="bi bi-trophy"></i> Active Election: <?php echo $dashboard_stats['election_title']; ?></h5>
                                        <a href="election_details.php?id=<?php echo $dashboard_stats['election_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bi bi-calendar-check fs-1 text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Status</h6>
                                                    <span class="badge bg-success">Active</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bi bi-people fs-1 text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Participation Rate</h6>
                                                    <div class="progress progress-thin mb-1">
                                                        <div class="progress-bar bg-<?php echo ($dashboard_stats['participation_percentage'] > 50) ? 'success' : 'warning'; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $dashboard_stats['participation_percentage']; ?>%">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo $dashboard_stats['total_voted']; ?> of <?php echo $dashboard_stats['total_voters']; ?> voters
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Users Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3 d-flex flex-column flex-md-row align-items-center justify-content-between">
                                    <h5 class="card-title mb-3 mb-md-0"><i class="bi bi-person-vcard profile-icon icon"></i>&nbsp;Students</h5>
                                    <div class="d-flex flex-column flex-md-row gap-2">
                                        <div class="search-box">
                                            <input type="text" id="searchStudents" class="form-control form-control-sm" placeholder="Search students...">
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                                                <i class="bi bi-funnel"></i> Filter
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                                <li><a class="dropdown-item filter-option active" href="#" data-filter="all">All Students</a></li>
                                                <li><a class="dropdown-item filter-option" href="#" data-filter="admin">Admins Only</a></li>
                                                <li><a class="dropdown-item filter-option" href="#" data-filter="student">Students Only</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="studentsTable">
                                            <thead>
                                                <tr>
                                                    <th width="100"><i class="bi bi-person-badge role-icon icon"></i>&nbsp;Profile</th>
                                                    <th><i class="bi bi-people-fill icon"></i>&nbsp;Name</th>
                                                    <th><i class="bi bi-buildings department-icon icon"></i>&nbsp;Department</th>
                                                    <th><i class="bi bi-person-bounding-box profile-icon icon"></i>&nbsp;Role</th>
                                                    <th><i class="bi bi-power action-icon icon"></i>&nbsp;Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr class="student-row" data-student-type="<?php echo isset($student['type']) ? $student['type'] : 'student'; ?>">
                        <td>
                            <?php if (!empty($student['profilePicture'])): ?>
                                <img src="assets/img/profile/students/<?php echo htmlspecialchars($student['profilePicture']); ?>" 
                                     class="user-avatar" 
                                     alt="Profile"
                                     onerror="this.onerror=null;this.parentNode.innerHTML='<div class=\'initials-avatar\'><?php echo isset($student['name']) ? strtoupper(substr($student['name'], 0, 1)) : ""; ?></div>'">
                            <?php else: ?>
                                <div class="initials-avatar">
                                    <?php echo isset($student['name']) ? strtoupper(substr($student['name'], 0, 1)) : ''; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold"><?php echo isset($student['name']) ? htmlspecialchars($student['name']) : ''; ?></span>
                                <small class="text-muted"><i class="bi bi-envelope-check mail-icon"></i>&nbsp;<?php echo isset($student['email']) ? htmlspecialchars($student['email']) : ''; ?></small>
                            </div>
                        </td>
                        <td><i class="bi bi-building-check icon"></i>&nbsp;<?php echo isset($student['department']) ? htmlspecialchars($student['department']) : ''; ?></td>
                        <td>
                            <?php if (isset($student['type']) && $student['type'] == 'admin'): ?>
                                <span class="badge bg-primary"><i class="bi bi-shield-check"></i> Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-mortarboard"></i> Student</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (isset($student['type']) && $student['type'] == 'admin'): ?>
                                    <button class="btn btn-outline-primary student-action" data-action="demote" data-id="<?php echo isset($student['studentID']) ? $student['studentID'] : ''; ?>">
                                        <i class="bi bi-arrow-down-circle"></i> Demote
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-primary student-action" data-action="promote" data-id="<?php echo isset($student['studentID']) ? $student['studentID'] : ''; ?>">
                                        <i class="bi bi-arrow-up-circle"></i> Promote
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary student-action" data-action="reset" data-id="<?php echo isset($student['studentID']) ? $student['studentID'] : ''; ?>">
                                    <i class="bi bi-key"></i> Reset
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><br>
             
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize theme from localStorage
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        
        // Listen for theme change events from header
        document.addEventListener('themeChanged', function(e) {
            document.documentElement.setAttribute('data-bs-theme', e.detail.theme);
        });
        
        // Search functionality - updated IDs
        const searchInput = document.getElementById('searchStudents');  // Updated ID
        const studentRows = document.querySelectorAll('#studentsTable tbody tr');  // Updated selector
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            studentRows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const isVisible = name.includes(searchTerm) || email.includes(searchTerm);
                row.style.display = isVisible ? '' : 'none';
            });
        });
        
        // Filter functionality - updated filter values
        const filterOptions = document.querySelectorAll('.filter-option');
        
        filterOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                
                // Update active state
                filterOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                // Update dropdown button text
                document.getElementById('filterDropdown').innerHTML = 
                    `<i class="bi bi-funnel"></i> ${this.textContent}`;
                
                // Apply filter - updated selector
                studentRows.forEach(row => {
                    const studentType = row.getAttribute('data-student-type');  // Updated attribute name
                    const isVisible = filter === 'all' || studentType === filter;
                    row.style.display = isVisible ? '' : 'none';
                });
            });
        });
        
        document.querySelectorAll('.student-action').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const studentId = this.getAttribute('data-id');
                
                if (action === 'promote' || action === 'demote') {
                    // Use modal instead of confirm
                    const roleChangeModal = new bootstrap.Modal(document.getElementById('roleChangeModal'));
                    const roleChangeTitle = document.getElementById('roleChangeTitle');
                    const roleChangeMessage = document.getElementById('roleChangeMessage');
                    const roleChangeAlert = document.getElementById('roleChangeAlert');
                    const roleChangeAlertMessage = document.getElementById('roleChangeAlertMessage');
                    const confirmRoleChangeBtn = document.getElementById('confirmRoleChangeBtn');
                    const roleChangeIcon = document.querySelector('.role-change-icon');
                    const modalHeader = document.getElementById('roleChangeModal').querySelector('.modal-header');
                    
                    // Clear previous event listeners
                    const newConfirmBtn = confirmRoleChangeBtn.cloneNode(true);
                    confirmRoleChangeBtn.parentNode.replaceChild(newConfirmBtn, confirmRoleChangeBtn);
                    
                    // Update modal content based on action
                    if (action === 'promote') {
                        modalHeader.classList.remove('bg-danger');
                        modalHeader.classList.add('bg-success');
                        roleChangeTitle.textContent = 'Promote to Admin';
                        roleChangeMessage.textContent = 'You are about to promote this student to an admin role. They will have full access to manage elections and system settings.';
                        roleChangeAlertMessage.textContent = 'Admin users can create and manage elections, categories, and other administrative tasks.';
                        roleChangeIcon.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i>';
                        roleChangeIcon.classList.remove('demote-icon');
                        roleChangeIcon.classList.add('promote-icon');
                        newConfirmBtn.classList.remove('btn-danger');
                        newConfirmBtn.classList.add('btn-success');
                        
                        // Store original button for reference
                        const originalButton = this;
                        
                        // Add event listener for confirm button
                        newConfirmBtn.addEventListener('click', function() {
                            // Show loading state
                            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                            this.disabled = true;
                            
                            // Hide modal
                            roleChangeModal.hide();
                            
                            // Execute promote action
                            executeRoleChange(originalButton, studentId, 'promote');
                        });
                    } else { // demote
                        modalHeader.classList.remove('bg-success');
                        modalHeader.classList.add('bg-danger');
                        roleChangeTitle.textContent = 'Demote to Student';
                        roleChangeMessage.textContent = 'You are about to remove admin privileges from this user. They will no longer be able to manage elections or access administrative features.';
                        roleChangeAlertMessage.textContent = 'If you demote yourself, you will be logged out and redirected to the login page.';
                        roleChangeIcon.innerHTML = '<i class="bi bi-arrow-down-circle-fill"></i>';
                        roleChangeIcon.classList.remove('promote-icon');
                        roleChangeIcon.classList.add('demote-icon');
                        newConfirmBtn.classList.remove('btn-success');
                        newConfirmBtn.classList.add('btn-danger');
                        
                        // Store original button for reference
                        const originalButton = this;
                        
                        // Add event listener for confirm button
                        newConfirmBtn.addEventListener('click', function() {
                            // Show loading state
                            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                            this.disabled = true;
                            
                            // Hide modal
                            roleChangeModal.hide();
                            
                            // Execute demote action
                            executeRoleChange(originalButton, studentId, 'demote');
                        });
                    }
                    
                    // Show modal
                    roleChangeModal.show();
                
                } else if (action === 'reset') {
                    const resetPasswordModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                    const confirmResetBtn = document.getElementById('confirmResetBtn');
                    
                    // Clear previous event listeners
                    const newConfirmBtn = confirmResetBtn.cloneNode(true);
                    confirmResetBtn.parentNode.replaceChild(newConfirmBtn, confirmResetBtn);
                    
                    // Add event listener for confirm button
                    newConfirmBtn.addEventListener('click', function() {
                        // Show loading state
                        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                        this.disabled = true;
                        
                        fetch('reset_student_password.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                student_id: studentId
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Show temporary password modal
                                const tempPasswordModal = new bootstrap.Modal(document.getElementById('tempPasswordModal'));
                                const tempPasswordField = document.getElementById('tempPasswordField');
                                const copyTempPasswordBtn = document.getElementById('copyTempPasswordBtn');
                                
                                tempPasswordField.value = data.temp_password;
                                
                                copyTempPasswordBtn.addEventListener('click', function() {
                                    tempPasswordField.select();
                                    document.execCommand('copy');
                                    
                                    // Show feedback
                                    const originalHtml = this.innerHTML;
                                    this.innerHTML = '<i class="bi bi-check"></i>';
                                    setTimeout(() => {
                                        this.innerHTML = originalHtml;
                                    }, 2000);
                                });
                                
                                tempPasswordModal.show();
                                showToast('Success', 'Password reset successful', 'success');
                            } else {
                                showToast('Error', data.message || 'Operation failed', 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Error', 'An error occurred while processing your request', 'danger');
                        })
                        .finally(() => {
                            this.innerHTML = '<i class="bi bi-key me-1"></i> Reset Password';
                            this.disabled = false;
                            resetPasswordModal.hide();
                        });
                    });
                    
                    // Show modal
                    resetPasswordModal.show();
                }
            });
        });

        // Helper function for executing role changes
        function executeRoleChange(buttonElement, studentId, action) {
            const originalText = buttonElement.innerHTML;
            buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            buttonElement.disabled = true;
            
            fetch('update_student_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    student_id: studentId,
                    action: action
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success toast notification
                    showToast('Success', `Student ${action === 'promote' ? 'promoted' : 'demoted'} successfully!`, 'success');
                    
                    if (data.logout_required) {
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        // Refresh the page to show changes after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                } else {
                    showToast('Error', data.message || 'Operation failed', 'danger');
                    if (data.error) console.error('Server error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred: ' + error.message, 'danger');
            })
            .finally(() => {
                buttonElement.innerHTML = originalText;
                buttonElement.disabled = false;
            });
        }
        
        // Create toast container if it doesn't exist
        if (!document.getElementById('toastContainer')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Function to show toast notifications
        function showToast(title, message, type = 'info') {
            const toastId = 'toast-' + Date.now();
            const html = `
                <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${type} text-white">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        <strong class="me-auto">${title}</strong>
                        <small>Just now</small>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            document.getElementById('toastContainer').insertAdjacentHTML('beforeend', html);
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
            
            toast.show();
            
            // Remove the toast from DOM after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        // Share button functionality
        document.getElementById('shareBtn').addEventListener('click', function() {
            const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
            shareModal.show();
        });

        // Copy link functionality
        document.getElementById('copyLinkBtn').addEventListener('click', function() {
            const shareLink = document.getElementById('shareLink');
            shareLink.select();
            document.execCommand('copy');
            showToast('Success', 'Link copied to clipboard!', 'success');
        });

        // Share buttons functionality
        document.getElementById('shareEmailBtn').addEventListener('click', function() {
            const url = document.getElementById('shareLink').value;
            window.location.href = `mailto:?subject=SmartVote Dashboard&body=${encodeURIComponent(url)}`;
        });

        document.getElementById('shareWhatsappBtn').addEventListener('click', function() {
            const url = document.getElementById('shareLink').value;
            window.open(`https://wa.me/?text=${encodeURIComponent(url)}`, '_blank');
        });

        document.getElementById('shareTelegramBtn').addEventListener('click', function() {
            const url = document.getElementById('shareLink').value;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}`, '_blank');
        });

        document.getElementById('shareTwitterBtn').addEventListener('click', function() {
            const url = document.getElementById('shareLink').value;
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`, '_blank');
        });

        // Export button functionality
        document.getElementById('exportBtn').addEventListener('click', function() {
            const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
            exportModal.show();
        });

        // Export submit functionality
        document.getElementById('exportSubmitBtn').addEventListener('click', function() {
            const format = document.querySelector('input[name="exportFormat"]:checked').value;
            const exportStats = document.getElementById('exportStats').checked;
            const exportStudents = document.getElementById('exportStudents').checked;
            const exportElections = document.getElementById('exportElections').checked;

            // Show loading state
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Exporting...';
            this.disabled = true;

            // Prepare export data
            const exportData = {
                format: format,
                data: {
                    stats: exportStats,
                    students: exportStudents,
                    elections: exportElections
                }
            };

            // Send export request
            fetch('export_dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(exportData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Export failed');
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `dashboard_export.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                
                // Show success message
                showToast('Success', 'Export completed successfully!', 'success');
                
                // Hide modal
                bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
            })
            .catch(error => {
                console.error('Export error:', error);
                showToast('Error', 'Failed to export data. Please try again.', 'danger');
            })
            .finally(() => {
                // Reset button state
                this.innerHTML = '<i class="bi bi-download me-2"></i>Export';
                this.disabled = false;
            });
        });
    });
    </script>
    
    <!-- Role Change Confirmation Modal -->
    <div class="modal fade" id="roleChangeModal" tabindex="-1" aria-labelledby="roleChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="roleChangeModalLabel"><i class="bi bi-shield-fill"></i> Role Change Confirmation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="role-icon-container mb-4">
                        <div class="role-change-icon"></div>
                    </div>
                    <h4 id="roleChangeTitle" class="mb-3"></h4>
                    <p id="roleChangeMessage" class="text-muted"></p>
                    <div class="alert alert-info my-3" id="roleChangeAlert">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="roleChangeAlertMessage"></span>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn" id="confirmRoleChangeBtn">
                        <i class="bi bi-check-circle me-1"></i> Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Password Reset Confirmation Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="resetPasswordModalLabel"><i class="bi bi-key-fill"></i> Password Reset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="password-icon-container mb-4">
                        <div class="password-reset-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                    </div>
                    <h4 class="mb-3">Reset Student Password</h4>
                    <p class="text-muted">You are about to reset the password for this student. A new temporary password will be generated.</p>
                    <div class="alert alert-info my-3">
                        <i class="bi bi-info-circle me-2"></i>
                        The student will need to use this temporary password for their next login.
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmResetBtn">
                        <i class="bi bi-key me-1"></i> Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Temporary Password Display Modal -->
    <div class="modal fade" id="tempPasswordModal" tabindex="-1" aria-labelledby="tempPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tempPasswordModalLabel"><i class="bi bi-check-circle-fill"></i> Password Reset Successful</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="success-icon-container mb-4">
                        <div class="success-icon">
                            <i class="bi bi-unlock-fill"></i>
                        </div>
                    </div>
                    <h4 class="mb-3">Temporary Password</h4>
                    <p class="text-muted">The student's password has been reset. Here is the temporary password:</p>
                    <div class="input-group mb-3">
                        <input type="text" id="tempPasswordField" class="form-control form-control-lg text-center" readonly>
                        <button class="btn btn-outline-primary" type="button" id="copyTempPasswordBtn">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Please securely communicate this password to the student.
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle me-1"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    /* Role Change Modal Specific Styles */
    .role-icon-container {
        display: flex;
        justify-content: center;
        padding: 20px 0;
    }
    
    .role-change-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
        color: white;
    }
    
    .promote-icon {
        background: linear-gradient(135deg, #4ade80, #22c55e);
    }
    
    .demote-icon {
        background: linear-gradient(135deg, #fb7185, #e11d48);
    }
    
    /* Password Reset Modal Specific Styles */
    .password-icon-container,
    .success-icon-container {
        display: flex;
        justify-content: center;
        padding: 20px 0;
    }
    
    .password-reset-icon,
    .success-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
        color: white;
    }
    
    .password-reset-icon {
        background: linear-gradient(135deg, #fbbf24, #d97706);
    }
    
    .success-icon {
        background: linear-gradient(135deg, #34d399, #059669);
    }
    
    @keyframes icon-float {
        0% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
        100% {
            transform: translateY(0px);
        }
    }
    
    #tempPasswordField {
        font-family: monospace;
        letter-spacing: 2px;
        font-weight: bold;
    }
    </style>     <?php include 'includes/footer.php'; ?>
    
    <!-- PWA Installation -->
    <script src="scripts/install-prompt.js"></script>
</body>
</html>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/Election/sw.js')
                .then(registration => {
                    console.log('ServiceWorker registration successful');
                })
                .catch(err => {
                    console.log('ServiceWorker registration failed: ', err);
                });
        });
    }
</script>