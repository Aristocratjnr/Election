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
            if (confirm(`Are you sure you want to ${action} this student?`)) {
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                this.disabled = true;
                
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
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        
        } else if (action === 'reset') {
            if (confirm('Reset password for this student? A temporary password will be generated.')) {
                // Show loading state
                const originalText = this.innerHTML;
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
                        // In development, show the temp password (remove in production)
                        showPasswordModal(data.temp_password);
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
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        }
    });
});

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
        
        // Function to show password modal
        function showPasswordModal(password) {
            // Create modal if it doesn't exist
            if (!document.getElementById('passwordModal')) {
                const modalHtml = `
                    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-warning text-white">
                                    <h5 class="modal-title"><i class="bi bi-key-fill me-2"></i>Temporary Password</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4 text-center">
                                    <p class="text-muted mb-3">The temporary password for this student is:</p>
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <input type="text" class="form-control form-control-lg text-center" id="tempPassword" value="${password}" readonly>
                                        <button class="btn btn-outline-primary ms-2" id="copyPasswordBtn" title="Copy">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Please communicate this password securely to the student.
                                    </div>
                                </div>
                                <div class="modal-footer border-0 justify-content-center">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-check-circle me-1"></i> Got it
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Add copy functionality
                document.getElementById('copyPasswordBtn').addEventListener('click', function() {
                    const passwordInput = document.getElementById('tempPassword');
                    passwordInput.select();
                    document.execCommand('copy');
                    
                    // Show feedback
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check"></i>';
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                    }, 2000);
                });
            } else {
                // Update password if modal already exists
                document.getElementById('tempPassword').value = password;
            }
            
            // Show the modal
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
            passwordModal.show();
        }
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Share functionality
        const shareBtn = document.getElementById('shareBtn');
        const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
        const copyLinkBtn = document.getElementById('copyLinkBtn');
        const shareLink = document.getElementById('shareLink');
        const shareEmailBtn = document.getElementById('shareEmailBtn');
        const shareWhatsappBtn = document.getElementById('shareWhatsappBtn');
        const shareTelegramBtn = document.getElementById('shareTelegramBtn');
        const shareTwitterBtn = document.getElementById('shareTwitterBtn');
        
        // Export functionality
        const exportBtn = document.getElementById('exportBtn');
        const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
        const exportSubmitBtn = document.getElementById('exportSubmitBtn');
        
        // Share button click event
        shareBtn.addEventListener('click', function() {
            shareModal.show();
        });
        
        // Copy link button click event
        copyLinkBtn.addEventListener('click', function() {
            shareLink.select();
            document.execCommand('copy');
            
            // Show feedback
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-circle"></i> Copied!';
            this.classList.remove('btn-info');
            this.classList.add('btn-success');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('btn-success');
                this.classList.add('btn-info');
            }, 2000);
        });
        
        // Email share button click event
        shareEmailBtn.addEventListener('click', function() {
            const subject = 'SmartVote Dashboard';
            const body = 'Check out the SmartVote Dashboard: ' + shareLink.value;
            window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        });
        
        // WhatsApp share button click event
        shareWhatsappBtn.addEventListener('click', function() {
            const text = 'Check out the SmartVote Dashboard: ' + shareLink.value;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        });
        
        // Telegram share button click event
        shareTelegramBtn.addEventListener('click', function() {
            const text = 'Check out the SmartVote Dashboard: ' + shareLink.value;
            window.open(`https://t.me/share/url?url=${encodeURIComponent(shareLink.value)}&text=${encodeURIComponent('SmartVote Dashboard')}`, '_blank');
        });
        
        // Twitter share button click event
        shareTwitterBtn.addEventListener('click', function() {
            const text = 'Check out the SmartVote Dashboard:';
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(shareLink.value)}`, '_blank');
        });
        
        // Export button click event
        exportBtn.addEventListener('click', function() {
            exportModal.show();
        });
        
        // Format selection animation
        document.querySelectorAll('input[name="exportFormat"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.export-format-option').forEach(option => {
                    option.classList.remove('border-primary', 'bg-light');
                });
                this.closest('.export-format-option').classList.add('border-primary', 'bg-light');
            });
        });
        
        // Trigger the change event on the checked radio button to highlight it initially
        document.querySelector('input[name="exportFormat"]:checked').dispatchEvent(new Event('change'));
        
        // Export submit button click event
        exportSubmitBtn.addEventListener('click', function() {
            const format = document.querySelector('input[name="exportFormat"]:checked').value;
            const includeStats = document.getElementById('exportStats').checked;
            const includeStudents = document.getElementById('exportStudents').checked;
            const includeElections = document.getElementById('exportElections').checked;
            
            if (!includeStats && !includeStudents && !includeElections) {
                showToast('Warning', 'Please select at least one data type to export', 'warning');
                return;
            }
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Exporting...';
            this.disabled = true;
            
            // Prepare data for export
            const exportData = {
                format: format,
                includeStats: includeStats,
                includeStudents: includeStudents,
                includeElections: includeElections
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
                    throw new Error('Network response was not ok');
                }
                return response.blob();
            })
            .then(blob => {
                // Create a download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                
                // Set filename based on format
                const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                a.download = `smartvote-dashboard-${timestamp}.${format}`;
                
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                
                // Show success notification
                showToast('Success', `Dashboard data exported as ${format.toUpperCase()} successfully`, 'success');
                
                // Close modal
                exportModal.hide();
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred while exporting the data', 'danger');
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
        
        // Function to show toast notifications (same as above)
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
            
            // Create toast container if it doesn't exist
            if (!document.getElementById('toastContainer')) {
                const toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }
            
            document.getElementById('toastContainer').insertAdjacentHTML('beforeend', html);
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
            
            toast.show();
            
            // Remove the toast from DOM after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
    });
    </script>
     <?php include 'includes/footer.php'; ?>
</body>
</html>