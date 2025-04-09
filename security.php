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

// Page title
$page_title = "Account Security";
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
                                <i class="bi bi-shield-lock-fill me-2"></i>
                                Account Security
                            </h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <button type="button" class="btn btn-sm btn-outline-danger" id="securityHelp">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Security Tips
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Security Options -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" 
                                                id="password-tab" 
                                                data-bs-toggle="tab" 
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
                                                data-bs-toggle="tab" 
                                                data-bs-target="#2fa-content" 
                                                type="button" 
                                                role="tab" 
                                                aria-selected="false">
                                            <i class="bi bi-phone-fill me-2"></i>
                                            Two-Factor Auth
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" 
                                                id="sessions-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#sessions-content" 
                                                type="button" 
                                                role="tab" 
                                                aria-selected="false">
                                            <i class="bi bi-pc-display me-2"></i>
                                            Active Sessions
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- Password Tab -->
                                    <div class="tab-pane fade show active" id="password-content" role="tabpanel" aria-labelledby="password-tab">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Current Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                                    <button class="btn btn-outline-secondary password-toggle" type="button" tabindex="-1">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <div class="invalid-feedback">
                                                        Please enter your current password.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="new_password" name="new_password" required 
                                                           minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                                                    <button class="btn btn-outline-secondary password-toggle" type="button" tabindex="-1">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <div class="invalid-feedback">
                                                        Password must be at least 8 characters with numbers, lowercase and uppercase letters.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                    <button class="btn btn-outline-secondary password-toggle" type="button" tabindex="-1">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <div class="invalid-feedback">
                                                        Passwords do not match.
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="password-strength mt-2 mb-4">
                                                <p class="mb-1 text-muted">Password strength:</p>
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <small class="text-muted mt-1 d-block">Use a strong password that includes numbers, letters, and special characters.</small>
                                            </div>
                                            
                                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button type="submit" name="update_password" class="btn btn-primary">
                                                    <i class="bi bi-shield-check me-1"></i>
                                                    Update Password
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- 2FA Tab -->
                                    <div class="tab-pane fade" id="2fa-content" role="tabpanel" aria-labelledby="2fa-tab">
                                        <div class="p-4 text-center">
                                            <img src="assets/img/2fa-illustration.svg" alt="2FA Illustration" class="img-fluid mb-3" style="max-width: 200px;">
                                            
                                            <h4 class="mb-3">Two-Factor Authentication</h4>
                                            <p class="text-muted mb-4">
                                                Add an extra layer of security to your account by enabling two-factor authentication. 
                                                Each time you sign in, you'll need your password and a verification code.
                                            </p>
                                            
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                <div class="form-check form-switch d-flex justify-content-center align-items-center mb-4">
                                                    <input class="form-check-input me-3" type="checkbox" id="enable_2fa" name="enable_2fa" value="1" 
                                                           <?php echo $has_2fa ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-medium" for="enable_2fa">
                                                        <?php echo $has_2fa ? 'Two-Factor Authentication is Enabled' : 'Enable Two-Factor Authentication'; ?>
                                                    </label>
                                                </div>
                                                
                                                <button type="submit" name="toggle_2fa" class="btn btn-primary px-4">
                                                    <i class="bi bi-<?php echo $has_2fa ? 'shield-x' : 'shield-check'; ?> me-1"></i>
                                                    <?php echo $has_2fa ? 'Disable 2FA' : 'Enable 2FA'; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Sessions Tab -->
                                    <div class="tab-pane fade" id="sessions-content" role="tabpanel" aria-labelledby="sessions-tab">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">Active Sessions</h5>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle me-1"></i>
                                                Revoke All
                                            </button>
                                        </div>
                                        
                                        <div class="list-group mb-4">
                                            <div class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                                <div class="d-flex gap-2 w-100 justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-laptop fs-4 me-3 text-primary"></i>
                                                        <div class="d-inline-block">
                                                            <h6 class="mb-0">Current Session (Windows)</h6>
                                                            <small class="text-body-secondary">
                                                                <?php echo $_SERVER['REMOTE_ADDR']; ?> • <?php echo date('M j, Y g:i a'); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-success rounded-pill">Active</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Activity -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-activity me-2"></i>
                                    Recent Security Activity
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php if (empty($activity_logs)): ?>
                                    <div class="list-group-item py-3">
                                        <p class="text-center text-muted mb-0">No recent activity found.</p>
                                    </div>
                                    <?php else: ?>
                                        <?php foreach ($activity_logs as $log): ?>
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($log['activity']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-globe2 me-1"></i>
                                                        <?php echo htmlspecialchars($log['ip_address']); ?>
                                                    </small>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('M j, g:i a', strtotime($log['timestamp'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white text-center py-3">
                                <a href="activity.php" class="text-decoration-none">View All Activity</a>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-shield-check me-2"></i>
                                    Security Status
                                </h5>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="security-icon me-3 <?php echo $has_2fa ? 'bg-success' : 'bg-warning'; ?>">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Two-Factor Authentication</h6>
                                        <span class="badge <?php echo $has_2fa ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $has_2fa ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="security-icon me-3 bg-success">
                                        <i class="bi bi-envelope-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Email Verified</h6>
                                        <span class="badge bg-success">Verified</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="security-icon me-3 bg-info">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Last Password Change</h6>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime('-30 days')); ?>
                                        </small>
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

<style>
.security-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.password-toggle {
    cursor: pointer;
}

.password-toggle i {
    pointer-events: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.querySelector('i').classList.remove('bi-eye');
                this.querySelector('i').classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                this.querySelector('i').classList.remove('bi-eye-slash');
                this.querySelector('i').classList.add('bi-eye');
            }
        });
    });
    
    // Password validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const progressBar = document.querySelector('.password-strength .progress-bar');
    
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            // Simple password strength meter
            const val = this.value;
            let strength = 0;
            
            if (val.length >= 8) strength += 25;
            if (val.match(/[a-z]/)) strength += 25;
            if (val.match(/[A-Z]/)) strength += 25;
            if (val.match(/\d/)) strength += 25;
            
            progressBar.style.width = strength + '%';
            
            if (strength <= 25) {
                progressBar.className = 'progress-bar bg-danger';
            } else if (strength <= 50) {
                progressBar.className = 'progress-bar bg-warning';
            } else if (strength <= 75) {
                progressBar.className = 'progress-bar bg-info';
            } else {
                progressBar.className = 'progress-bar bg-success';
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
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Security tips modal
    document.getElementById('securityHelp').addEventListener('click', function() {
        const modalHtml = `
            <div class="modal fade" id="securityTipsModal" tabindex="-1" aria-labelledby="securityTipsLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="securityTipsLabel">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                Security Best Practices
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-key me-2"></i>Strong Passwords</h6>
                                    <p>Use a unique, complex password with at least 12 characters, including uppercase letters, lowercase letters, numbers, and symbols.</p>
                                    
                                    <h6><i class="bi bi-phone me-2"></i>Enable Two-Factor Authentication</h6>
                                    <p>Adding 2FA provides a crucial second layer of security beyond your password.</p>
                                    
                                    <h6><i class="bi bi-wifi me-2"></i>Secure Connections</h6>
                                    <p>Only log in from secure, private networks. Avoid public Wi-Fi for admin tasks.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-laptop me-2"></i>Keep Your Device Secure</h6>
                                    <p>Use up-to-date antivirus software and keep your operating system patched.</p>
                                    
                                    <h6><i class="bi bi-envelope me-2"></i>Beware of Phishing</h6>
                                    <p>Be cautious of suspicious emails asking for credentials. We will never ask for your password via email.</p>
                                    
                                    <h6><i class="bi bi-door-closed me-2"></i>Always Log Out</h6>
                                    <p>Always log out when you've finished your session, especially on shared computers.</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
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
});
</script>

<?php include 'includes/footer.php'; ?> 