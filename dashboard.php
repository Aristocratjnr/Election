<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check authentication
session_start();
if (!isset($_SESSION['login_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
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

// Fetch dashboard data with error handling
try {
    // Check if tables exist
    $tables_exist = $conn->query("SHOW TABLES LIKE 'elections'")->num_rows > 0 
                 && $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0;
    
    if ($tables_exist) {
        $query = "
            SELECT
                (SELECT COUNT(*) FROM elections) AS total_elections,
                IFNULL((SELECT COUNT(*) FROM categories c WHERE c.election_id = e.id AND e.status = 1), 0) AS total_active_categories,
                (SELECT COUNT(*) FROM users WHERE type = 1) AS total_voters,
                COUNT(DISTINCT v.voter_id) AS total_voted,
                e.title AS election_title,
                e.id AS election_id
            FROM
                elections e
            LEFT JOIN
                users u ON u.type = 1
            LEFT JOIN
                votes v ON e.id = v.election_id
            WHERE
                e.status = 1
            LIMIT 1";

        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
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
    }
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error_message = "Error loading dashboard data. Please try again later.";
}

// Fetch users data
$users = [];
try {
    $users_query = $conn->prepare("SELECT * FROM users");
    $users_query->execute();
    $users_result = $users_query->get_result();
    $users = $users_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Users query error: " . $e->getMessage());
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
       <di class="main-content">
        <?php include 'includes/header.php'; ?>
        <br>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
          
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
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
                                <h5 class="card-title mb-3 mb-md-0">System Users</h5>
                                <div class="d-flex flex-column flex-md-row gap-2">
                                    <div class="search-box">
                                        <input type="text" id="searchUsers" class="form-control form-control-sm" placeholder="Search users...">
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                                            <i class="bi bi-funnel"></i> Filter
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                            <li><a class="dropdown-item filter-option active" href="#" data-filter="all">All Users</a></li>
                                            <li><a class="dropdown-item filter-option" href="#" data-filter="admin">Admins Only</a></li>
                                            <li><a class="dropdown-item filter-option" href="#" data-filter="user">Regular Users Only</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="usersTable">
                                        <thead>
                                            <tr>
                                                <th width="80">Profile</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                            <tr class="user-row" data-user-type="<?php echo ($user['type'] == 0) ? 'admin' : 'user'; ?>">
                                                <td>
                                                    <?php if (!empty($user['profile_picture'])): ?>
                                                        <img src="assets/img/profile/users/<?php echo $user['profile_picture']; ?>" class="user-avatar" alt="Profile">
                                                    <?php else: ?>
                                                        <div class="initials-avatar">
                                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold"><?php echo htmlspecialchars($user['name']); ?></span>
                                                        <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['type'] == 0): ?>
                                                        <span class="badge bg-primary">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">User</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($user['type'] == 0): ?>
                                                            <button class="btn btn-outline-primary user-action" data-action="demote" data-id="<?php echo $user['id']; ?>">
                                                                <i class="bi bi-arrow-down-circle"></i> Demote
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-outline-primary user-action" data-action="promote" data-id="<?php echo $user['id']; ?>">
                                                                <i class="bi bi-arrow-up-circle"></i> Promote
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-outline-secondary user-action" data-action="reset" data-id="<?php echo $user['id']; ?>">
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
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchUsers');
        const userRows = document.querySelectorAll('#usersTable tbody tr');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            userRows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const isVisible = name.includes(searchTerm) || email.includes(searchTerm);
                row.style.display = isVisible ? '' : 'none';
            });
        });
        
        // Filter functionality
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
                
                // Apply filter
                userRows.forEach(row => {
                    const userType = row.getAttribute('data-user-type');
                    const isVisible = filter === 'all' || userType === filter;
                    row.style.display = isVisible ? '' : 'none';
                });
            });
        });
        
        // User actions
        document.querySelectorAll('.user-action').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const userId = this.getAttribute('data-id');
                
                if (action === 'promote' || action === 'demote') {
                    if (confirm(`Are you sure you want to ${action} this user?`)) {
                        // AJAX call to update user role
                        fetch('update_user_role.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                action: action
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + (data.message || 'Operation failed'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred');
                        });
                    }
                } else if (action === 'reset') {
                    if (confirm('Reset password for this user?')) {
                        // AJAX call to reset password
                        fetch('reset_user_password.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                user_id: userId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Password reset successful');
                            } else {
                                alert('Error: ' + (data.message || 'Operation failed'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred');
                        });
                    }
                }
            });
        });
    });
    </script>
</body>
</html>