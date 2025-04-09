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
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">
                                <i class="bi bi-activity me-2"></i>
                                Activity Log
                            </h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="exportLog">
                                        <i class="bi bi-download me-1"></i>
                                        Export
                                    </button>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to clear logs older than 30 days? This action cannot be undone.');">
                                        <button type="submit" name="clear_logs" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>
                                            Clear Old Logs
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter Bar -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="d-flex gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search activities..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                            
                            <select name="filter" class="form-select" style="max-width: 150px;" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Activities</option>
                                <option value="login" <?php echo $filter === 'login' ? 'selected' : ''; ?>>Login Events</option>
                                <option value="security" <?php echo $filter === 'security' ? 'selected' : ''; ?>>Security Events</option>
                                <option value="election" <?php echo $filter === 'election' ? 'selected' : ''; ?>>Election Events</option>
                                <option value="user" <?php echo $filter === 'user' ? 'selected' : ''; ?>>User Management</option>
                                <option value="self" <?php echo $filter === 'self' ? 'selected' : ''; ?>>My Activities</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="text-muted">
                            Showing <?php echo min(($page - 1) * $limit + 1, $total_rows); ?> - <?php echo min($page * $limit, $total_rows); ?> of <?php echo $total_rows; ?> activities
                        </div>
                    </div>
                </div>
                
                <!-- Activity Log Table -->
                <div class="card border-0 shadow-sm mb-4">
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
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-info-circle me-2"></i>
                                                No activity logs found
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($activity_logs as $log): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="activity-indicator <?php echo getActivityClass($log['activity']); ?>"></span>
                                                    <span><?php echo date('M j, Y g:i a', strtotime($log['timestamp'])); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($log['adminID'] == $admin_id): ?>
                                                <span class="badge bg-primary-subtle text-primary">You</span>
                                                <?php else: ?>
                                                <?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['activity']); ?></td>
                                            <td><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link view-details" 
                                                        data-bs-toggle="modal" data-bs-target="#activityDetailModal"
                                                        data-activity-id="<?php echo $log['id']; ?>"
                                                        data-timestamp="<?php echo date('M j, Y g:i:s a', strtotime($log['timestamp'])); ?>"
                                                        data-admin="<?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?>"
                                                        data-activity="<?php echo htmlspecialchars($log['activity']); ?>"
                                                        data-ip="<?php echo htmlspecialchars($log['ip_address']); ?>">
                                                    View
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
                <nav aria-label="Activity log pagination">
                    <ul class="pagination justify-content-center">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityDetailModalLabel">Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Timestamp</label>
                    <p id="modal-timestamp" class="mb-1">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Admin</label>
                    <p id="modal-admin" class="mb-1">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Activity</label>
                    <p id="modal-activity" class="mb-1">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">IP Address</label>
                    <p id="modal-ip" class="mb-1">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">User Agent</label>
                    <p class="mb-1">
                        <small class="text-muted"><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']); ?></small>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.activity-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 10px;
}

.activity-login {
    background-color: #0d6efd;
}

.activity-security {
    background-color: #dc3545;
}

.activity-election {
    background-color: #198754;
}

.activity-user {
    background-color: #fd7e14;
}

.activity-default {
    background-color: #6c757d;
}
</style>

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

include 'includes/footer.php';
?> 