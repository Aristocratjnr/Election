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
$success_message = "";
$error_message = "";

// Fetch admin data
$stmt = $conn->prepare("SELECT * FROM admins WHERE adminID = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$has_2fa = !empty($admin_data['two_factor_secret']);

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif (!password_verify($current_password, $admin_data['password'])) {
        $error_message = "Current password is incorrect.";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE adminID = ?");
        $update_stmt->bind_param("si", $hashed_password, $admin_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Password updated successfully.";
            
            // Log activity
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (adminID, activity, ip_address) VALUES (?, ?, ?)");
            $activity = "Password changed";
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("iss", $admin_id, $activity, $ip);
            $log_stmt->execute();
        } else {
            $error_message = "Failed to update password: " . $conn->error;
        }
    }
}

// Handle 2FA toggle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_2fa'])) {
    // For demo purposes only - would need a proper 2FA implementation
    $enable_2fa = isset($_POST['enable_2fa']) && $_POST['enable_2fa'] == '1';
    
    if ($enable_2fa && !$has_2fa) {
        // Generate a secret key and update 2FA
        $two_factor_secret = bin2hex(random_bytes(16)); // Simplified for demo
        
        $update_stmt = $conn->prepare("UPDATE admins SET two_factor_secret = ? WHERE adminID = ?");
        $update_stmt->bind_param("si", $two_factor_secret, $admin_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Two-factor authentication enabled.";
            $has_2fa = true;
            
            // Log activity
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (adminID, activity, ip_address) VALUES (?, ?, ?)");
            $activity = "2FA enabled";
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("iss", $admin_id, $activity, $ip);
            $log_stmt->execute();
        } else {
            $error_message = "Failed to enable 2FA: " . $conn->error;
        }
    } elseif (!$enable_2fa && $has_2fa) {
        // Disable 2FA
        $update_stmt = $conn->prepare("UPDATE admins SET two_factor_secret = NULL WHERE adminID = ?");
        $update_stmt->bind_param("i", $admin_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Two-factor authentication disabled.";
            $has_2fa = false;
            
            // Log activity
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (adminID, activity, ip_address) VALUES (?, ?, ?)");
            $activity = "2FA disabled";
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_stmt->bind_param("iss", $admin_id, $activity, $ip);
            $log_stmt->execute();
        } else {
            $error_message = "Failed to disable 2FA: " . $conn->error;
        }
    }
}

// Fetch recent activity logs
$log_stmt = $conn->prepare("SELECT * FROM admin_activity_log WHERE adminID = ? ORDER BY timestamp DESC LIMIT 10");
$log_stmt->bind_param("i", $admin_id);
$log_stmt->execute();
$activity_logs = $log_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get admin last password change date if available
$last_password_change = null;
$pw_log_stmt = $conn->prepare("SELECT timestamp FROM admin_activity_log WHERE adminID = ? AND activity = 'Password changed' ORDER BY timestamp DESC LIMIT 1");
$pw_log_stmt->bind_param("i", $admin_id);
$pw_log_stmt->execute();
$pw_result = $pw_log_stmt->get_result();
if ($pw_result->num_rows > 0) {
    $last_password_change = $pw_result->fetch_assoc()['timestamp'];
} else {
    // If no password change log exists, use admin creation date
    $last_password_change = $admin_data['created_at'];
}

// Get email verification status from admin data
$email_verified = !empty($admin_data['email']) ? true : false;

// Page title
$page_title = "Account Security";
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
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
    }
    
    body {
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f8f9fc;
        color: #333;
    }
    
    .main-content {
        padding-top: 1.5rem;
        padding-bottom: 3rem;
    }
    
    /* Card styling */
    .card {
        border: none;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        transition: all 0.2s;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e3e6f0;
    }
    
    /* Nav pills */
    .nav-pills .nav-link {
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        color: var(--secondary-color);
    }
    
    .nav-pills .nav-link.active {
        background-color: var(--primary-color);
        color: white;
    }
    
    /* Form controls */
    .form-control {
        padding: 0.75rem 1rem;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    /* Password strength meter */
    .password-strength .progress {
        height: 6px;
        border-radius: 3px;
    }
    
    .password-strength .progress-bar {
        position: relative;
        border-radius: 3px;
    }
    
    .password-strength .progress-bar::after {
        content: '';
        position: absolute;
        right: 0;
        top: -3px;
        width: 12px;
        height: 12px;
        background-color: inherit;
        border-radius: 50%;
        transform: translateX(50%);
    }
    
    /* Security icons */
    .security-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .security-icon-primary {
        background-color: rgba(78, 115, 223, 0.1);
        color: var(--primary-color);
    }
    
    .security-icon-success {
        background-color: rgba(28, 200, 138, 0.1);
        color: var(--success-color);
    }
    
    .security-icon-warning {
        background-color: rgba(246, 194, 62, 0.1);
        color: var(--warning-color);
    }
    
    .security-icon-info {
        background-color: rgba(54, 185, 204, 0.1);
        color: var(--info-color);
    }
    
    /* Password toggle */
    .password-toggle {
        cursor: pointer;
        transition: all 0.2s;
        border-left: none;
    }
    
    .password-toggle:hover {
        background-color: #f8f9fa;
    }
    
    .password-toggle i {
        pointer-events: none;
    }
    
    /* Activity log items */
    .activity-item {
        border-left: 3px solid var(--primary-color);
        padding-left: 1rem;
        transition: all 0.2s;
    }
    
    .activity-item:hover {
        background-color: #f8f9fa;
    }
    
    /* Badges */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    /* Buttons */
    .btn {
        padding: 0.5rem 1.25rem;
        border-radius: 0.35rem;
        font-weight: 500;
    }
    
    /* Alert styling */
    .alert {
        border-radius: 0.35rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .card-body {
            padding: 1.25rem;
        }
        
        .nav-pills .nav-link {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    }
    
    /* Animation for form validation */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
    
    .shake-animation {
        animation: shake 0.5s ease-in-out;
    }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                                <div>
                                    <h1 class="h2 fw-bold text-dark mb-1">
                                        <i class="bi bi-shield-lock me-2 text-primary"></i>
                                        Account Security
                                    </h1>
                                    <p class="text-muted mb-0">Manage your account security settings and monitor activity</p>
                                </div>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="securityHelp">
                                            <i class="bi bi-question-circle me-1"></i>
                                            Security Guide
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav aria-label="breadcrumb" class="mb-4">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Security</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                        <div>
                            <?php echo $success_message; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <div>
                            <?php echo $error_message; ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row g-4">
                        <!-- Security Options -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <ul class="nav nav-pills card-header-pills" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" 
                                                    id="password-tab" 
                                                    data-bs-toggle="pill" 
                                                    data-bs-target="#password-content" 
                                                    type="button" 
                                                    role="tab" 
                                                    aria-selected="true">
                                                <i class="bi bi-key-fill me-2"></i>
                                                Password
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" 
                                                    id="2fa-tab" 
                                                    data-bs-toggle="pill" 
                                                    data-bs-target="#2fa-content" 
                                                    type="button" 
                                                    role="tab" 
                                                    aria-selected="false">
                                                <i class="bi bi-shield-lock me-2"></i>
                                                2FA
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" 
                                                    id="sessions-tab" 
                                                    data-bs-toggle="pill" 
                                                    data-bs-target="#sessions-content" 
                                                    type="button" 
                                                    role="tab" 
                                                    aria-selected="false">
                                                <i class="bi bi-devices me-2"></i>
                                                Sessions
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-0">
                                    <div class="tab-content p-4">
                                        <!-- Password Tab -->
                                        <div class="tab-pane fade show active" id="password-content" role="tabpanel" aria-labelledby="password-tab">
                                            <div class="mb-4">
                                                <h5 class="fw-semibold mb-3">Change Password</h5>
                                                <p class="text-muted">Update your account password regularly to maintain security.</p>
                                            </div>
                                            
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                                <div class="mb-4">
                                                    <label for="current_password" class="form-label fw-medium">Current Password</label>
                                                    <div class="input-group has-validation">
                                                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                                        <input type="password" class="form-control py-2" id="current_password" name="current_password" required>
                                                        <button class="btn btn-light password-toggle" type="button" tabindex="-1">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <div class="invalid-feedback">
                                                            Please enter your current password.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <label for="new_password" class="form-label fw-medium">New Password</label>
                                                    <div class="input-group has-validation">
                                                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                                        <input type="password" class="form-control py-2" id="new_password" name="new_password" required 
                                                               minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                                                        <button class="btn btn-light password-toggle" type="button" tabindex="-1">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <div class="invalid-feedback">
                                                            Password must be at least 8 characters with numbers, lowercase and uppercase letters.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <label for="confirm_password" class="form-label fw-medium">Confirm New Password</label>
                                                    <div class="input-group has-validation">
                                                        <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                                        <input type="password" class="form-control py-2" id="confirm_password" name="confirm_password" required>
                                                        <button class="btn btn-light password-toggle" type="button" tabindex="-1">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <div class="invalid-feedback">
                                                            Passwords do not match.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="password-strength mb-4">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small class="text-muted fw-medium">Password strength</small>
                                                        <small class="text-muted" id="strength-text">Weak</small>
                                                    </div>
                                                    <div class="progress rounded-pill" style="height: 6px;">
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted mt-2 d-block">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Use 8+ characters with uppercase, lowercase, numbers & symbols
                                                    </small>
                                                </div>
                                                
                                                <div class="d-grid">
                                                    <button type="submit" name="update_password" class="btn btn-primary py-2">
                                                        <i class="bi bi-shield-check me-2"></i>
                                                        Update Password
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <!-- 2FA Tab -->
                                        <div class="tab-pane fade" id="2fa-content" role="tabpanel" aria-labelledby="2fa-tab">
                                            <div class="text-center py-4">
                                                <div class="mb-4 mx-auto" style="max-width: 300px;">
                                                    <img src="assets/img/2fa-illustration.svg" alt="2FA Illustration" class="img-fluid">
                                                </div>
                                                
                                                <h5 class="fw-semibold mb-3">Two-Factor Authentication</h5>
                                                <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                                                    Protect your account with an extra layer of security. When you sign in, you'll be required to provide your password and a verification code.
                                                </p>
                                                
                                                <div class="card bg-light border-0 mb-4">
                                                    <div class="card-body p-4">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <h6 class="mb-1 fw-medium">Two-Factor Authentication</h6>
                                                                <p class="text-muted small mb-0">
                                                                    <?php echo $has_2fa ? 'Currently enabled' : 'Currently disabled'; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa" value="1" 
                                                                       <?php echo $has_2fa ? 'checked' : ''; ?> style="width: 3em; height: 1.5em;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                    <input type="hidden" name="enable_2fa" value="<?php echo $has_2fa ? '0' : '1'; ?>">
                                                    <button type="submit" name="toggle_2fa" class="btn btn-primary px-4 py-2">
                                                        <i class="bi bi-<?php echo $has_2fa ? 'shield-x' : 'shield-check'; ?> me-2"></i>
                                                        <?php echo $has_2fa ? 'Disable 2FA' : 'Enable 2FA'; ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <!-- Sessions Tab -->
                                        <div class="tab-pane fade" id="sessions-content" role="tabpanel" aria-labelledby="sessions-tab">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div>
                                                    <h5 class="fw-semibold mb-1">Active Sessions</h5>
                                                    <p class="text-muted small mb-0">Manage your logged-in devices</p>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger" id="revokeAllBtn">
                                                    <i class="bi bi-x-circle me-1"></i>
                                                    Revoke All
                                                </button>
                                            </div>
                                            
                                            <div class="list-group mb-4">
                                                <div class="list-group-item list-group-item-action rounded-3 mb-2">
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <div class="bg-primary bg-opacity-10 p-3 rounded-2">
                                                            <i class="bi bi-laptop text-primary fs-4"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0 fw-medium">Current Session (<?php echo php_uname('s'); ?>)</h6>
                                                                <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                                            </div>
                                                            <small class="text-muted">
                                                                <i class="bi bi-globe2 me-1"></i>
                                                                <?php echo $_SERVER['REMOTE_ADDR']; ?>
                                                            </small>
                                                            <div class="mt-1">
                                                                <small class="text-muted">
                                                                    <i class="bi bi-clock me-1"></i>
                                                                    <?php 
                                                                        // Use actual session start time if available, otherwise use current time
                                                                        echo isset($_SESSION['login_time']) ? date('M j, Y g:i a', strtotime($_SESSION['login_time'])) : date('M j, Y g:i a'); 
                                                                    ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info bg-light border-0">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                                    <div>
                                                        <h6 class="alert-heading mb-1">Session Security</h6>
                                                        <p class="small mb-0">If you notice any suspicious activity, revoke all sessions immediately and change your password.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Activity -->
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0 fw-semibold">
                                        <i class="bi bi-activity me-2 text-primary"></i>
                                        Recent Activity
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php if (empty($activity_logs)): ?>
                                        <div class="list-group-item py-4 text-center">
                                            <div class="py-3">
                                                <i class="bi bi-activity text-muted fs-1 opacity-25"></i>
                                                <p class="text-muted mt-2 mb-0">No recent activity found</p>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                            <?php foreach ($activity_logs as $log): ?>
                                            <div class="list-group-item border-0 py-3 activity-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <div class="mb-1">
                                                        <h6 class="fw-medium mb-1"><?php echo htmlspecialchars($log['activity']); ?></h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-globe2 me-1"></i>
                                                            <?php echo htmlspecialchars($log['ip_address']); ?>
                                                        </small>
                                                    </div>
                                                    <small class="text-muted text-nowrap ps-2">
                                                        <?php echo date('M j, g:i a', strtotime($log['timestamp'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <a href="activity.php" class="text-decoration-none fw-medium">
                                        View All Activity
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0 fw-semibold">
                                        <i class="bi bi-shield-check me-2 text-primary"></i>
                                        Security Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="security-icon security-icon-<?php echo $has_2fa ? 'success' : 'warning'; ?>">
                                                <i class="bi bi-shield-lock fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 fw-medium">Two-Factor Authentication</h6>
                                            <span class="badge bg-<?php echo $has_2fa ? 'success' : 'warning'; ?> bg-opacity-10 text-<?php echo $has_2fa ? 'success' : 'warning'; ?>">
                                                <?php echo $has_2fa ? 'Enabled' : 'Disabled'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="security-icon security-icon-<?php echo $email_verified ? 'success' : 'warning'; ?>">
                                                <i class="bi bi-envelope-check fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 fw-medium">Email Verified</h6>
                                            <span class="badge bg-<?php echo $email_verified ? 'success' : 'warning'; ?> bg-opacity-10 text-<?php echo $email_verified ? 'success' : 'warning'; ?>">
                                                <?php echo $email_verified ? 'Verified' : 'Not Verified'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="security-icon security-icon-info">
                                                <i class="bi bi-calendar-check fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 fw-medium">Last Password Change</h6>
                                            <small class="text-muted">
                                                <?php 
                                                    echo $last_password_change ? date('M j, Y', strtotime($last_password_change)) : 'Never changed';
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="alert alert-warning bg-light border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                                            <div>
                                                <h6 class="alert-heading mb-1">Security Tip</h6>
                                                <p class="small mb-0">Change your password every 60-90 days and never reuse old passwords.</p>
                                            </div>
                                        </div>
                                    </div>
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
        // Toggle password visibility
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                    this.classList.add('active');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                    this.classList.remove('active');
                }
            });
        });
        
        // Session management
        document.getElementById('revokeAllBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to revoke all sessions? You will be logged out.')) {
                window.location.href = 'logout.php?revoke_all=true';
            }
        });
        
        // Password strength meter with more detailed feedback
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const progressBar = document.querySelector('.password-strength .progress-bar');
        const strengthText = document.getElementById('strength-text');
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                const val = this.value;
                let strength = 0;
                let suggestions = [];
                
                // Length check
                if (val.length >= 12) strength += 30;
                else if (val.length >= 8) strength += 15;
                else suggestions.push('Use more characters');
                
                // Lowercase check
                if (val.match(/[a-z]/)) strength += 10;
                else suggestions.push('Add lowercase letters');
                
                // Uppercase check
                if (val.match(/[A-Z]/)) strength += 10;
                else suggestions.push('Add uppercase letters');
                
                // Number check
                if (val.match(/\d/)) strength += 10;
                else suggestions.push('Add numbers');
                
                // Special char check
                if (val.match(/[^a-zA-Z0-9]/)) strength += 10;
                else suggestions.push('Add special characters');
                
                // Sequence/repeat check
                if (!val.match(/(.)\1{2,}/)) strength += 10;
                else suggestions.push('Avoid repeated characters');
                
                // Common password check
                const commonPasswords = ['password', '123456', 'qwerty', 'admin'];
                if (!commonPasswords.includes(val.toLowerCase())) strength += 20;
                else suggestions.push('Avoid common passwords');
                
                // Cap at 100
                strength = Math.min(strength, 100);
                
                // Update progress bar
                progressBar.style.width = strength + '%';
                
                // Update strength text and color
                if (strength <= 30) {
                    progressBar.className = 'progress-bar bg-danger';
                    strengthText.textContent = 'Weak';
                    strengthText.className = 'text-muted text-danger';
                } else if (strength <= 60) {
                    progressBar.className = 'progress-bar bg-warning';
                    strengthText.textContent = 'Moderate';
                    strengthText.className = 'text-muted text-warning';
                } else if (strength <= 80) {
                    progressBar.className = 'progress-bar bg-info';
                    strengthText.textContent = 'Strong';
                    strengthText.className = 'text-muted text-info';
                } else {
                    progressBar.className = 'progress-bar bg-success';
                    strengthText.textContent = 'Very Strong';
                    strengthText.className = 'text-muted text-success';
                }
                
                // Check if passwords match
                if (confirmPassword.value) {
                    confirmPassword.setCustomValidity(
                        confirmPassword.value !== this.value ? 'Passwords do not match.' : ''
                    );
                }
            });
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                this.setCustomValidity(
                    this.value !== newPassword.value ? 'Passwords do not match.' : ''
                );
            });
        }
        
        // Form validation with better UX
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Add shake animation to invalid fields
                    form.querySelectorAll(':invalid').forEach(el => {
                        el.classList.add('is-invalid', 'shake-animation');
                        el.addEventListener('animationend', () => {
                            el.classList.remove('shake-animation');
                        }, { once: true });
                        
                        // Scroll to first invalid field
                        if (el === form.querySelector(':invalid')) {
                            el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    });
                }
                
                form.classList.add('was-validated');
            }, false);
        });
        
        // Security tips modal with more content
        document.getElementById('securityHelp').addEventListener('click', function() {
            const modalHtml = `
                <div class="modal fade" id="securityTipsModal" tabindex="-1" aria-labelledby="securityTipsLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white border-0">
                                <h5 class="modal-title fw-semibold" id="securityTipsLabel">
                                    <i class="bi bi-shield-check me-2"></i>
                                    Security Best Practices
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex mb-3">
                                            <div class="flex-shrink-0 text-primary me-3">
                                                <i class="bi bi-key fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Password Security</h6>
                                                <p class="small text-muted mb-0">
                                                    Use long, unique passwords (12+ characters) with a mix of character types. 
                                                    Consider using a password manager to generate and store them securely.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex mb-3">
                                            <div class="flex-shrink-0 text-primary me-3">
                                                <i class="bi bi-phone fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Two-Factor Authentication</h6>
                                                <p class="small text-muted mb-0">
                                                    Always enable 2FA for an extra security layer. Use authenticator apps instead of SMS when possible.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 text-primary me-3">
                                                <i class="bi bi-browser-edge fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Browser Security</h6>
                                                <p class="small text-muted mb-0">
                                                    Keep your browser updated and use security extensions. Avoid saving passwords in browsers.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="d-flex mb-3">
                                            <div class="flex-shrink-0 text-primary me-3">
                                                <i class="bi bi-envelope fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Phishing Protection</h6>
                                                <p class="small text-muted mb-0">
                                                    Be wary of suspicious emails. Never click links or download attachments from unknown senders.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex mb-3">
                                            <div class="flex-shrink-0 text-primary me-3">
                                                <i class="bi bi-wifi fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Network Security</h6>
                                                <p class="small text-muted mb-0">
                                                    Use VPN on public Wi-Fi. Ensure your home network uses WPA3 encryption and a strong password.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 text-primary me-3">
                                                <i class="bi bi-shield-exclamation fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-semibold mb-1">Account Monitoring</h6>
                                                <p class="small text-muted mb-0">
                                                    Regularly review your account activity and enable notifications for suspicious logins.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-light border mt-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-lightbulb text-warning fs-4 me-3"></i>
                                        <div>
                                            <p class="mb-0 fw-medium">Tip: Security is an ongoing process. Stay informed about the latest threats and best practices.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-primary px-4 py-2" data-bs-dismiss="modal">
                                    <i class="bi bi-check-circle me-2"></i>
                                    I Understand
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const securityModal = new bootstrap.Modal(document.getElementById('securityTipsModal'));
            securityModal.show();
            
            // Remove modal from DOM after hiding
            document.getElementById('securityTipsModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        });
        
        // Add animation class to elements when they scroll into view
        const animateOnScroll = () => {
            document.querySelectorAll('.card, .list-group-item').forEach(el => {
                const elTop = el.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elTop < windowHeight * 0.75) {
                    el.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        };
        
        // Run once on load and then on scroll
        animateOnScroll();
        window.addEventListener('scroll', animateOnScroll);
    });
    </script>
</body>
</html>