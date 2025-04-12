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
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
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

// Prepare filter conditions
$where_clause = "";
$params = [];
$types = "";

if ($filter === 'login') {
    $where_clause = " WHERE activity LIKE ?";
    $params[] = '%login%';
    $types .= "s";
} elseif ($filter === 'security') {
    $where_clause = " WHERE activity LIKE ? OR activity LIKE ?";
    $params[] = '%password%';
    $params[] = '%security%';
    $types .= "ss";
} elseif ($filter === 'election') {
    $where_clause = " WHERE activity LIKE ?";
    $params[] = '%election%';
    $types .= "s";
} elseif ($filter === 'user') {
    $where_clause = " WHERE activity LIKE ?";
    $params[] = '%user%';
    $types .= "s";
} elseif ($filter === 'self') {
    $where_clause = " WHERE adminID = ?";
    $params[] = $admin_id;
    $types .= "i";
}

// Add search condition if provided
if (!empty($search)) {
    $where_clause = empty($where_clause) ? " WHERE " : $where_clause . " AND ";
    $where_clause .= "(activity LIKE ? OR ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM admin_activity_log" . $where_clause;
$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch activity logs with pagination
$log_sql = "SELECT l.*, a.name as admin_name 
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

// Clear logs function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clear_logs'])) {
    // Optionally, you could archive instead of delete
    $clear_sql = "DELETE FROM admin_activity_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $clear_stmt = $conn->prepare($clear_sql);
    
    if ($clear_stmt->execute()) {
        $success_message = "Old activity logs cleared successfully.";
        
        // Log this activity
        $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (adminID, activity, ip_address) VALUES (?, ?, ?)");
        $activity = "Cleared old activity logs";
        $ip = $_SERVER['REMOTE_ADDR'];
        $log_stmt->bind_param("iss", $admin_id, $activity, $ip);
        $log_stmt->execute();
        
        // Refresh the page to show updated logs
        header("Location: activity.php");
        exit();
    } else {
        $error_message = "Failed to clear logs: " . $conn->error;
    }
}

// Page title
$page_title = "Activity Log";
include 'includes/header.php';
?><br><br>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --border-radius: 0.35rem;
        --box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    body {
        background-color: #f8f9fc;
        color: #5a5c69;
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    .sidebar {
        background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
        min-height: 100vh;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: all 0.3s ease;
    }


    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.35rem;
    }

    .table-responsive {
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        border-bottom-width: 1px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        color: var(--secondary-color);
        background-color: #f8f9fc;
    }

    .table tbody tr {
        transition: all 0.15s ease;
    }


    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
    }

    .activity-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 10px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
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
        background-color: #2e59d9;
        border-color: #2653d4;
    }

    .btn-outline-secondary {
        color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-outline-secondary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-outline-danger {
        color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .btn-outline-danger:hover {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .page-link {
        color: var(--primary-color);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fc;
    }

    .modal-content {
        border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-bottom: 1px solid #e3e6f0;
    }

    .modal-footer {
        border-top: 1px solid #e3e6f0;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #2e59d9;
    }

    /* Animation for table rows */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .table tbody tr {
        animation: fadeIn 0.3s ease forwards;
    }

    .table tbody tr:nth-child(1) { animation-delay: 0.05s; }
    .table tbody tr:nth-child(2) { animation-delay: 0.1s; }
    .table tbody tr:nth-child(3) { animation-delay: 0.15s; }
    .table tbody tr:nth-child(4) { animation-delay: 0.2s; }
    .table tbody tr:nth-child(5) { animation-delay: 0.25s; }
    /* Continue for more rows if needed */

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            border: 1px solid #e3e6f0;
            border-radius: var(--border-radius);
        }
        
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0;
            border-radius: var(--border-radius);
        }
        
        .table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--secondary-color);
            margin-right: 1rem;
        }
        
        .table tbody td:last-child {
            border-bottom: none;
        }
    }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-12 ms-auto col-lg-10 px-md-6 py-3">
            <div class="container-fluid">
                <!-- Header Section -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 text-gray-800">
                        <i class="fas fa-history me-2"></i>
                        Activity Log
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="exportLog">
                                <i class="fas fa-download me-1"></i>
                                Export
                            </button>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear logs older than 30 days? This action cannot be undone.');">
                                <button type="submit" name="clear_logs" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash me-1"></i>
                                    Clear Old Logs
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3">
                                    <div class="col-md-8">
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
                                    <div class="col-md-4">
                                        <select name="filter" class="form-select" onchange="this.form.submit()">
                                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Activities</option>
                                            <option value="login" <?php echo $filter === 'login' ? 'selected' : ''; ?>>Login Events</option>
                                            <option value="security" <?php echo $filter === 'security' ? 'selected' : ''; ?>>Security Events</option>
                                            <option value="election" <?php echo $filter === 'election' ? 'selected' : ''; ?>>Election Events</option>
                                            <option value="user" <?php echo $filter === 'user' ? 'selected' : ''; ?>>User Management</option>
                                            <option value="self" <?php echo $filter === 'self' ? 'selected' : ''; ?>>My Activities</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="text-gray-600">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Showing <?php echo min(($page - 1) * $limit + 1, $total_rows); ?>-<?php echo min($page * $limit, $total_rows); ?> of <?php echo $total_rows; ?> activities
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Log Table -->
                <div class="card shadow-sm mb-4">
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
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-info-circle fa-2x mb-3"></i>
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
                                            <td data-label="Activity"><?php echo htmlspecialchars($log['activity']); ?></td>
                                            <td data-label="IP Address"><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                                            <td data-label="Details">
                                                <button type="button" class="btn btn-sm btn-outline-primary view-details" 
                                                        data-bs-toggle="modal" data-bs-target="#activityDetailModal"
                                                        data-activity-id="<?php echo $log['id']; ?>"
                                                        data-timestamp="<?php echo date('M j, Y g:i:s a', strtotime($log['timestamp'])); ?>"
                                                        data-admin="<?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>"
                                                        data-activity="<?php echo htmlspecialchars($log['activity']); ?>"
                                                        data-ip="<?php echo htmlspecialchars($log['ip_address']); ?>">
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
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Activity log pagination" class="d-flex justify-content-center">
                    <ul class="pagination">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php
                        $start_page = max(1, min($page - 2, $total_pages - 4));
                        $end_page = min($total_pages, max($page + 2, 5));
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
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
    <div class="modal-dialog modal-dialog-centered">
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
                            <label class="form-label fw-bold text-gray-600">Admin</label>
                            <p id="modal-admin" class="mb-1">-</p>
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
                            <p class="mb-1 text-muted small">
                                <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?>
                            </p>
                        </div>
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

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View details modal
    const viewButtons = document.querySelectorAll('.view-details');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const timestamp = this.getAttribute('data-timestamp');
            const admin = this.getAttribute('data-admin');
            const activity = this.getAttribute('data-activity');
            const ip = this.getAttribute('data-ip');
            
            document.getElementById('modal-timestamp').textContent = timestamp;
            document.getElementById('modal-admin').textContent = admin;
            document.getElementById('modal-activity').textContent = activity;
            document.getElementById('modal-ip').textContent = ip;
        });
    });
    
    // Export activity log
    document.getElementById('exportLog').addEventListener('click', function() {
        // Get current filter and search parameters
        const urlParams = new URLSearchParams(window.location.search);
        const filter = urlParams.get('filter') || 'all';
        const search = urlParams.get('search') || '';
        
        // Prepare data for CSV
        const rows = [
            ['Timestamp', 'Admin', 'Activity', 'IP Address'] // Header row
        ];
        
        // Add table data
        document.querySelectorAll('table tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 4) {
                const timestamp = cells[0].textContent.trim();
                const admin = cells[1].textContent.trim();
                const activity = cells[2].textContent.trim();
                const ip = cells[3].textContent.trim();
                
                rows.push([timestamp, admin, activity, ip]);
            }
        });
        
        // Convert to CSV
        const csvContent = rows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        const date = new Date().toISOString().slice(0, 10);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `activity_log_${filter}_${date}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>

<?php
// Helper function to determine activity class for indicator
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

?>