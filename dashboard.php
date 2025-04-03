<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check authentication
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html'); // Redirect to login page if not logged in
    exit();
}

// Database connection - ensure this file exists and has correct credentials
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

// Fetch dashboard data with better error handling
try {
    // First verify the connection is working
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if tables exist with more detailed error reporting
    $tables = ['elections', 'categories', 'students', 'votes']; // Updated to include students
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$check || $check->num_rows == 0) {
            throw new Exception("Table '$table' doesn't exist");
        }
    }

    // Modified query to work with students table
    $query = "
    SELECT
      (SELECT COUNT(*) FROM elections) AS total_elections,
      IFNULL((SELECT COUNT(*) FROM categories c WHERE c.electionID = e.electionID AND e.status = 'Ongoing'), 0) AS total_active_categories,
      (SELECT COUNT(*) FROM students WHERE status = 'Active') AS total_voters,
      COUNT(DISTINCT v.studentID) AS total_voted,
      e.name AS election_title,
      e.electionID AS election_id
    FROM
        elections e
    LEFT JOIN
        students s ON s.status = 'Active'
    LEFT JOIN
        votes v ON e.electionID = v.electionID
    WHERE
        e.status = 'Ongoing'
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
    $error_message = "Error loading dashboard data: " . $e->getMessage(); // More detailed error
}

// Fetch students data with error handling
$students = [];
try {
    if (!$conn) {
        throw new Exception("No database connection");
    }
    
    $students_query = $conn->prepare("SELECT studentID, name, email, password, dateOfBirth, department, contactNumber, registrationDate, status, created_at, role as type, profilePicture FROM students");
    if (!$students_query) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$students_query->execute()) {
        throw new Exception("Execute failed: " . $students_query->error);
    }
    
    $students_result = $students_query->get_result();
    if (!$students_result) {
        throw new Exception("Get result failed: " . $students_query->error);
    }
    
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include 'includes/header.php'; ?>
                <br>
                
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4"><br>
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"></h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                                <i class="bi bi-calendar"></i> This week
                            </button>
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
                                    <h5 class="card-title text-muted">Elections</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-primary-light me-3">
                                            <i class="bi bi-box-seam-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo $dashboard_stats['total_elections']; ?></h2>
                                            <p class="text-muted mb-0">Total Elections</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted">Categories</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-success-light me-3">
                                            <i class="bi bi-bookmark-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo $dashboard_stats['total_active_categories']; ?></h2>
                                            <p class="text-muted mb-0">Active Categories</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Voters Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted">Voters</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon bg-info-light me-3">
                                            <i class="bi bi-people-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h2 class="mb-0"><?php echo $dashboard_stats['total_voters']; ?></h2>
                                            <p class="text-muted mb-0">Registered Voters</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Participation Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted">Participation</h5>
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
                                            <p class="text-muted mb-1">Votes Cast</p>
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
                                        <a href="election_details.php?id=<?php echo $dashboard_stats['election_id']; ?>" class="btn btn-sm btn-primary">
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
                <h5 class="card-title mb-3 mb-md-0">Students</h5>
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
                                <th width="80">Profile</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Actions</th>
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
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Refresh the page to show changes
                        location.reload();
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
     <?php include 'includes/footer.php'; ?>
</body>
</html>