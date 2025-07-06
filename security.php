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
    $enable_2fa = isset($_POST['enable_2fa']) && $_POST['enable_2fa'] == '1';
    
    if ($enable_2fa && !$has_2fa) {
        $two_factor_secret = bin2hex(random_bytes(16));
        
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

// Get admin last password change date
$last_password_change = null;
$pw_log_stmt = $conn->prepare("SELECT timestamp FROM admin_activity_log WHERE adminID = ? AND activity = 'Password changed' ORDER BY timestamp DESC LIMIT 1");
$pw_log_stmt->bind_param("i", $admin_id);
$pw_log_stmt->execute();
$pw_result = $pw_log_stmt->get_result();
if ($pw_result->num_rows > 0) {
    $last_password_change = $pw_result->fetch_assoc()['timestamp'];
} else {
    $last_password_change = $admin_data['created_at'];
}

// Get email verification status
$email_verified = !empty($admin_data['email']) ? true : false;

// Calculate security score (0-100)
$security_score = 40; // Base score
if ($has_2fa) $security_score += 30;
if ($email_verified) $security_score += 15;
if (strtotime($last_password_change) > strtotime('-30 days')) $security_score += 15;

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
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
    :root {
        --primary-color: #4e73df;
        --primary-light: rgba(78, 115, 223, 0.1);
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --border-radius: 0.5rem;
        --box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        --transition: all 0.3s ease;
    }
    
    body {
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f5f7fb;
        color: #333;
    }
    
    .security-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        transition: var(--transition);
    }
    
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
    }
    
    /* Security Status Card */
    .security-status-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--box-shadow);
        margin-bottom: 1.5rem;
    }
    
    .status-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .security-score {
        text-align: center;
    }
    
    .score-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .score-progress {
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        margin-top: 0.5rem;
        overflow: hidden;
    }
    
    .score-progress .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-color), #8e44ad);
        transition: width 0.6s ease;
    }
    
    .status-items {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .status-item {
        padding: 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        background: var(--light-color);
        transition: var(--transition);
    }
    
    .status-item i {
        font-size: 1.25rem;
        margin-right: 0.75rem;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .status-item.verified i {
        background: rgba(28, 200, 138, 0.1);
        color: var(--success-color);
    }
    
    .status-item.active i {
        background: rgba(78, 115, 223, 0.1);
        color: var(--primary-color);
    }
    
    .status-item.warning i {
        background: rgba(246, 194, 62, 0.1);
        color: var(--warning-color);
    }
    
    .status-item span:first-of-type {
        flex-grow: 1;
        font-weight: 500;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        border-radius: 50px;
    }
    
    .verified-badge {
        background: rgba(28, 200, 138, 0.1);
        color: var(--success-color);
    }
    
    .active-badge {
        background: rgba(78, 115, 223, 0.1);
        color: var(--primary-color);
    }
    
    .warning-badge {
        background: rgba(246, 194, 62, 0.1);
        color: var(--warning-color);
    }
    
    /* Password Form */
    .password-form-container {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--box-shadow);
        margin-bottom: 1.5rem;
    }
    
    .form-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--dark-color);
        display: flex;
        align-items: center;
    }
    
    .form-title i {
        margin-right: 0.75rem;
        color: var(--primary-color);
    }
    
    .password-input {
        margin-bottom: 1rem;
    }
    
    .password-input .input-group-text {
        background: var(--light-color);
        border-right: none;
    }
    
    .password-input .form-control {
        border-left: none;
    }
    
    .password-toggle {
        cursor: pointer;
        transition: var(--transition);
    }
    
    .password-toggle:hover {
        background: #f8f9fa;
    }
    
    /* Password Strength Meter */
    .password-strength-meter {
        margin: 1.5rem 0;
    }
    
    .strength-labels {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .strength-value {
        font-weight: 600;
    }
    
    .strength-indicator {
        height: 6px;
        border-radius: 3px;
        background: #f0f0f0;
        overflow: hidden;
    }
    
    .strength-bar {
        height: 100%;
        width: 0;
        transition: width 0.3s ease, background 0.3s ease;
    }
    
    /* Activity Log */
    .activity-log {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }
    
    .activity-item {
        padding: 1rem 1.5rem;
        border-left: 3px solid var(--primary-color);
        transition: var(--transition);
    }
    
    .activity-item:hover {
        background: rgba(78, 115, 223, 0.03);
    }
    
    .activity-item + .activity-item {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .activity-content {
        display: flex;
        justify-content: space-between;
    }
    
    .activity-text {
        font-weight: 500;
    }
    
    .activity-meta {
        display: flex;
        font-size: 0.875rem;
        color: var(--secondary-color);
    }
    
    .activity-meta span {
        display: flex;
        align-items: center;
        margin-right: 1rem;
    }
    
    .activity-meta i {
        margin-right: 0.25rem;
    }
    
    /* Tabs */
    .security-tabs .nav-link {
        border: none;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        color: var(--secondary-color);
        border-radius: 0;
        position: relative;
    }
    
    .security-tabs .nav-link.active {
        color: var(--primary-color);
        background: transparent;
    }
    
    .security-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--primary-color);
    }
    
    .security-tabs .nav-link i {
        margin-right: 0.5rem;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 767.98px) {
        .status-items {
            grid-template-columns: 1fr;
        }
        
        .activity-content {
            flex-direction: column;
        }
        
        .activity-meta {
            margin-top: 0.5rem;
        }
    }
    
    /* Animations */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
    
    .shake-animation {
        animation: shake 0.5s ease-in-out;
    }
    
    /* Floating Action Button */
    .fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(78, 115, 223, 0.3);
        z-index: 100;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .fab:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 24px rgba(78, 115, 223, 0.4);
    }
    
    .fab i {
        font-size: 1.5rem;
    }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-9 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-3 mb-4">
                    <div>
                        <h1 class="h2 fw-bold mb-1">
                            <i class="bi bi-shield-lock me-2 text-primary"></i>
                            Account Security
                        </h1>
                        <p class="text-muted mb-0">Manage your account security settings and monitor activity</p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" id="securityHelp">
                            <i class="bi bi-question-circle me-1"></i>
                            Security Guide
                        </button>
                    </div>
                </div>
                
                <!-- Alerts -->
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
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        
                        <!-- Security Tabs -->
                        <div class="card security-card">
                            <div class="card-header bg-transparent border-0 p-0">
                                <ul class="nav security-tabs" id="securityTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-tab-pane" type="button" role="tab">
                                            <i class="bi bi-key"></i>
                                            Password
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="2fa-tab" data-bs-toggle="tab" data-bs-target="#2fa-tab-pane" type="button" role="tab">
                                            <i class="bi bi-shield-lock"></i>
                                            2FA
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions-tab-pane" type="button" role="tab">
                                            <i class="bi bi-devices"></i>
                                            Sessions
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-4">
                                <div class="tab-content" id="securityTabsContent">
                                    <!-- Password Tab -->
                                    <div class="tab-pane fade show active" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                            <div class="mb-4">
                                                <label for="current_password" class="form-label fw-medium">Current Password</label>
                                                <div class="input-group password-input has-validation">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                                    <button class="btn btn-outline-secondary password-toggle" type="button">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <div class="invalid-feedback">
                                                        Please enter your current password.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="new_password" class="form-label fw-medium">New Password</label>
                                                <div class="input-group password-input has-validation">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="new_password" name="new_password" required 
                                                           minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                                                    <button class="btn btn-outline-secondary password-toggle" type="button">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <div class="invalid-feedback">
                                                        Password must be at least 8 characters with numbers, lowercase and uppercase letters.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="confirm_password" class="form-label fw-medium">Confirm New Password</label>
                                                <div class="input-group password-input has-validation">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                    <button class="btn btn-outline-secondary password-toggle" type="button">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <div class="invalid-feedback">
                                                        Passwords do not match.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="password-strength-meter">
                                                <div class="strength-labels">
                                                    <span>Password Strength</span>
                                                    <span class="strength-value text-danger">Weak</span>
                                                </div>
                                                <div class="strength-indicator">
                                                    <div class="strength-bar bg-danger"></div>
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Use 12+ characters with uppercase, lowercase, numbers & symbols
                                                </small>
                                            </div>
                                            
                                            <div class="d-grid mt-4">
                                                <button type="submit" name="update_password" class="btn btn-primary py-2">
                                                    <i class="bi bi-shield-check me-2"></i>
                                                    Update Password
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- 2FA Tab -->
                                    <div class="tab-pane fade" id="2fa-tab-pane" role="tabpanel" aria-labelledby="2fa-tab">
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
                                    <div class="tab-pane fade" id="sessions-tab-pane" role="tabpanel" aria-labelledby="sessions-tab">
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
                    
                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Activity Log -->
                        <div class="card activity-log">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="card-title mb-0 fw-semibold">
                                    <i class="bi bi-activity me-2 text-primary"></i>
                                    Recent Activity
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($activity_logs)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-activity text-muted fs-1 opacity-25 mb-3"></i>
                                    <p class="text-muted">No recent activity found</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($activity_logs as $log): ?>
                                    <div class="activity-item">
                                        <div class="activity-content">
                                            <div class="activity-text"><?php echo htmlspecialchars($log['activity']); ?></div>
                                            <div class="activity-meta">
                                                <span>
                                                    <i class="bi bi-globe2"></i>
                                                    <?php echo htmlspecialchars($log['ip_address']); ?>
                                                </span>
                                                <span>
                                                    <i class="bi bi-clock"></i>
                                                    <?php echo date('M j, g:i a', strtotime($log['timestamp'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent border-0 text-center py-3">
                                <a href="activity.php" class="text-decoration-none fw-medium">
                                    View All Activity
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Security Tips -->
                        <div class="card mt-4">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="card-title mb-0 fw-semibold">
                                    <i class="bi bi-lightbulb me-2 text-warning"></i>
                                    Security Tips
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1 fw-medium">Use a Password Manager</h6>
                                        <p class="small text-muted mb-0">Generate and store complex passwords securely.</p>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-start mb-3">
                                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1 fw-medium">Enable 2FA</h6>
                                        <p class="small text-muted mb-0">Add an extra layer of protection to your account.</p>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1 fw-medium">Regular Updates</h6>
                                        <p class="small text-muted mb-0">Change passwords every 60-90 days.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab" id="securityHelpFab">
        <i class="bi bi-question-lg"></i>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
        
        // Password strength meter
        const newPassword = document.getElementById('new_password');
        const strengthBar = document.querySelector('.strength-bar');
        const strengthText = document.querySelector('.strength-value');
        
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                const val = this.value;
                let strength = 0;
                
                // Length check
                if (val.length >= 12) strength += 30;
                else if (val.length >= 8) strength += 15;
                
                // Character type checks
                if (val.match(/[a-z]/)) strength += 10;
                if (val.match(/[A-Z]/)) strength += 10;
                if (val.match(/\d/)) strength += 10;
                if (val.match(/[^a-zA-Z0-9]/)) strength += 10;
                
                // Common password check
                const commonPasswords = ['password', '123456', 'qwerty', 'admin'];
                if (!commonPasswords.includes(val.toLowerCase())) strength += 20;
                
                // Update UI
                strengthBar.style.width = Math.min(strength, 100) + '%';
                
                if (strength <= 30) {
                    strengthBar.className = 'strength-bar bg-danger';
                    strengthText.className = 'strength-value text-danger';
                    strengthText.textContent = 'Weak';
                } else if (strength <= 60) {
                    strengthBar.className = 'strength-bar bg-warning';
                    strengthText.className = 'strength-value text-warning';
                    strengthText.textContent = 'Moderate';
                } else if (strength <= 80) {
                    strengthBar.className = 'strength-bar bg-info';
                    strengthText.className = 'strength-value text-info';
                    strengthText.textContent = 'Strong';
                } else {
                    strengthBar.className = 'strength-bar bg-success';
                    strengthText.className = 'strength-value text-success';
                    strengthText.textContent = 'Very Strong';
                }
            });
        }
        
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    form.querySelectorAll(':invalid').forEach(el => {
                        el.classList.add('is-invalid', 'shake-animation');
                        el.addEventListener('animationend', () => {
                            el.classList.remove('shake-animation');
                        }, { once: true });
                        
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
        
        // Session management
        document.getElementById('revokeAllBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to revoke all sessions? You will be logged out.')) {
                window.location.href = 'logout.php?revoke_all=true';
            }
        });
        
        // Security help modal
        function showSecurityTips() {
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
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const securityModal = new bootstrap.Modal(document.getElementById('securityTipsModal'));
            securityModal.show();
            
            document.getElementById('securityTipsModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
        
        // Attach to both button and FAB
        document.getElementById('securityHelp').addEventListener('click', showSecurityTips);
        document.getElementById('securityHelpFab').addEventListener('click', showSecurityTips);
        
        // Animate elements on scroll
        const animateOnScroll = () => {
            document.querySelectorAll('.card, .list-group-item').forEach(el => {
                const elTop = el.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elTop < windowHeight * 0.75) {
                    el.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        };
        
        animateOnScroll();
        window.addEventListener('scroll', animateOnScroll);
    });
    </script>
</body>
</html>