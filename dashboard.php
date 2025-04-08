<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        
        .card-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
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
            color: #6c757d;
            z-index: 10;
        }
        .search-box input {
            padding-left: 30px;
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
            background-color: #f8f9fa;
            font-weight: bold;
            color: #6c757d;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .export-format-option:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd;
        }
        .export-format-option input:checked + label {
            color: #0d6efd;
            font-weight: 500;
        }
        .export-format-option input:checked + label i {
            color: #0d6efd;
        }
        .list-group-item {
            transition: all 0.2s ease;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        .form-switch .form-check-input {
            cursor: pointer;
        }
        .form-switch .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
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
                        <h1 class="h2"></h1>
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
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="shareModalLabel"><i class="bi bi-share-fill me-2"></i>Share Dashboard</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted mb-3">Share this dashboard with others:</p>
                                    <div class="input-group mb-4">
                                        <input type="text" class="form-control form-control-lg" id="shareLink" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" readonly>
                                        <button class="btn btn-primary" type="button" id="copyLinkBtn">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <h6 class="mb-3">Share via:</h6>
                                    <div class="d-flex justify-content-center gap-3">
                                        <button class="btn btn-outline-primary rounded-circle p-3 share-btn" id="shareEmailBtn" title="Email">
                                            <i class="bi bi-envelope-fill fs-4"></i>
                                        </button>
                                        <button class="btn btn-outline-success rounded-circle p-3 share-btn" id="shareWhatsappBtn" title="WhatsApp">
                                            <i class="bi bi-whatsapp fs-4"></i>
                                        </button>
                                        <button class="btn btn-outline-primary rounded-circle p-3 share-btn" id="shareFacebookBtn" title="Facebook">
                                            <i class="bi bi-facebook fs-4"></i>
                                        </button>
                                        <button class="btn btn-outline-info rounded-circle p-3 share-btn" id="shareTwitterBtn" title="Twitter">
                                            <i class="bi bi-twitter-x fs-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Modal -->
                    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="exportModalLabel"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i>Export Dashboard Data</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <p class="text-muted mb-4">Choose the format and data to export:</p>
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
                                                        <i class="bi bi-graph-up me-2"></i>
                                                        Dashboard Statistics
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="exportStats" checked>
                                                    </div>
                                                </label>
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-people me-2"></i>
                                                        Students List
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="exportStudents" checked>
                                                    </div>
                                                </label>
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-check2-square me-2"></i>
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
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                            <div class="card border-0 shadow-sm h-100">
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
                            <div class="card border-0 shadow-sm h-100">
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
                            <div class="card border-0 shadow-sm h-100">
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
                            <div class="card border-0 shadow-sm h-100">
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
                                        <h5 class="card-title mb-0">Active Election: <?php echo $dashboard_stats['election_title']; ?></h5>
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
                                <th> <i class="bi bi-buildings department-icon icon"></i>&nbsp;Department</th>
                                <th> <i class="bi bi-person-bounding-box profile-icon icon"></i>&nbsp;Role</th>
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
                <small class="text-muted"> <i class="bi bi-envelope-check mail-icon"></i>&nbsp;<?php echo isset($student['email']) ? htmlspecialchars($student['email']) : ''; ?></small>
            </div>
        </td>
        <td><i class="bi bi-building-check icon"></i>&nbsp;<?php echo isset($student['department']) ? htmlspecialchars($student['department']) : ''; ?></td>
        <td>
            <?php if (isset($student['type']) && $student['type'] == 'admin'): ?>
                <span class="badge bg-primary">Admin</span>
            <?php else: ?>
                <span class="badge bg-secondary">Student</span>
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
                        if (data.logout_required) {
                            window.location.href = 'login.php';
                        } else {
                            // Refresh the page to show changes
                            location.reload();
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Operation failed'));
                        if (data.error) console.error('Server error:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred: ' + error.message);
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
                        alert(`Password reset successful. Temporary password: ${data.temp_password}`);
                    } else {
                        alert('Error: ' + (data.message || 'Operation failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your request');
                })
                .finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        }
    });
});
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
        const shareFacebookBtn = document.getElementById('shareFacebookBtn');
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
            this.innerHTML = '<i class="bi bi-check"></i> Copied!';
            setTimeout(() => {
                this.innerHTML = originalText;
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
        
        // Facebook share button click event
        shareFacebookBtn.addEventListener('click', function() {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareLink.value)}`, '_blank');
        });
        
        // Twitter share button click event
        shareTwitterBtn.addEventListener('click', function() {
            const text = 'Check out the SmartVote Dashboard';
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(shareLink.value)}`, '_blank');
        });
        
        // Export button click event
        exportBtn.addEventListener('click', function() {
            exportModal.show();
        });
        
        // Export submit button click event
        exportSubmitBtn.addEventListener('click', function() {
            const format = document.querySelector('input[name="exportFormat"]:checked').value;
            const includeStats = document.getElementById('exportStats').checked;
            const includeStudents = document.getElementById('exportStudents').checked;
            const includeElections = document.getElementById('exportElections').checked;
            
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
                
                // Close modal
                exportModal.hide();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while exporting the data');
            })
            .finally(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });
    </script>
     <?php include 'includes/footer.php'; ?>
</body>
</html>