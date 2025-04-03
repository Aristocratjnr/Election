<?php
// Start secure session with stricter settings
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Database connection
require 'configs/dbconnection.php';

// Initialize variables
$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$is_valid = false;
$user_email = '';

// Check if token is valid
if (!empty($token)) {
    try {
        // Verify token format first (64 hex chars)
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            $error = 'Invalid token format';
        } else {
            // Check token in database with prepared statement
            $stmt = $conn->prepare("SELECT pr.*, s.email 
                                   FROM password_resets pr
                                   JOIN Students s ON pr.user_id = s.studentID
                                   WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()");
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $reset_request = $result->fetch_assoc();
                $is_valid = true;
                $user_email = $reset_request['email'];
                
                // Store minimal data in session
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_user_id'] = $reset_request['user_id'];
                $_SESSION['reset_expires'] = time() + 900; // 15-minute window
            } else {
                $error = 'Invalid or expired reset link. Please request a new one.';
            }
        }
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
        // Consider rate limiting here
    }
} else {
    $error = 'No reset token provided.';
}

// Process password reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify session matches token
    $session_token = $_SESSION['reset_token'] ?? '';
    $session_user_id = $_SESSION['reset_user_id'] ?? 0;
    $session_expires = $_SESSION['reset_expires'] ?? 0;
    
    if (empty($session_token) || $session_token !== $token || $session_user_id <= 0 || time() > $session_expires) {
        $error = 'Session expired. Please restart the reset process.';
        $is_valid = false;
    } else {
        $new_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please enter and confirm your new password';
        } elseif (strlen($new_password) < 12) {  // Increased minimum length
            $error = 'Password must be at least 12 characters long';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (!preg_match('/[A-Z]/', $new_password) || 
                 !preg_match('/[a-z]/', $new_password) || 
                 !preg_match('/[0-9]/', $new_password) || 
                 !preg_match('/[^A-Za-z0-9]/', $new_password)) {
            $error = 'Password must include uppercase, lowercase, number, and special character';
        } else {
            try {
                // Check if password was previously used (optional but recommended)
                $stmt = $conn->prepare("SELECT password FROM Students WHERE studentID = ?");
                $stmt->bind_param('i', $session_user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (password_verify($new_password, $user['password'])) {
                    $error = 'You cannot reuse an old password';
                } else {
                    // Hash the new password with current best practices
                    $hashed_password = password_hash($new_password, PASSWORD_ARGON2ID);
                    
                    // Begin transaction for atomic updates
                    $conn->begin_transaction();
                    
                    try {
                        // Update user password
                        $stmt = $conn->prepare("UPDATE Students SET password = ?, last_password_change = NOW() WHERE studentID = ?");
                        $stmt->bind_param('si', $hashed_password, $session_user_id);
                        $stmt->execute();
                        
                        // Mark token as used
                        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                        $stmt->bind_param('s', $token);
                        $stmt->execute();
                        
                        // Invalidate all other active sessions for this user (security measure)
                        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ?");
                        $stmt->bind_param('i', $session_user_id);
                        $stmt->execute();
                        
                        $conn->commit();
                        
                        // Clear session variables
                        unset($_SESSION['reset_token']);
                        unset($_SESSION['reset_user_id']);
                        unset($_SESSION['reset_expires']);
                        
                        // Regenerate session ID after privilege change
                        session_regenerate_id(true);
                        
                        $success = 'Your password has been reset successfully! Please login with your new password.';
                        $is_valid = false;
                    } catch (Exception $e) {
                        $conn->rollback();
                        throw $e;
                    }
                }
            } catch (Exception $e) {
                error_log("Password update error: " . $e->getMessage());
                $error = 'An error occurred while resetting your password. Please try again.';
            }
        }
    }
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SmartVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
        }
        
        body {
            background-color: #f5f7ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 15px 0;
        }
        
        .auth-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .auth-header img {
            height: 50px;
            margin-bottom: 1rem;
        }
        
        .auth-header h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .auth-footer {
            text-align: center;
            padding: 1rem 2rem;
            border-top: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--success-color);
            margin-bottom: 1.5rem;
        }
        
        .password-strength {
            height: 5px;
            background-color: #e9ecef;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
        
        .password-criteria {
            margin-top: 10px;
            font-size: 0.85rem;
        }
        
        .criteria-item {
            color: #6c757d;
            transition: all 0.3s;
        }
        
        .criteria-item.met {
            color: var(--success-color);
        }
        
        @media (max-width: 576px) {
            .auth-container {
                border-radius: 10px;
                margin: 15px;
                width: calc(100% - 30px);
            }
            
            .auth-header,
            .auth-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <img src="assets/img/logo.png" alt="SmartVote Logo">
                <h2>Reset Password</h2>
                <?php if ($is_valid): ?>
                    <p>Set a new password for <?php echo htmlspecialchars($user_email); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                   
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <h3>Password Reset!</h3>
                        <p class="text-muted"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-muted"><?php echo $success; ?></p>
                        <a href="login.php" class="btn btn-primary mt-3 px-4">Login Now</a>
                    </div>
                    <?php elseif ($is_valid): ?>
                    <form method="POST" action="reset-password.php" id="resetPasswordForm">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <form method="POST" action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>" id="resetPasswordForm">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required
                                       placeholder="Enter new password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="password-strength-bar"></div>
                            </div>
                            <div class="password-criteria mt-2">
                                <div class="criteria-item" id="length-criteria">
                                    <i class="bi bi-circle"></i> At least 8 characters
                                </div>
                                <div class="criteria-item" id="uppercase-criteria">
                                    <i class="bi bi-circle"></i> At least 1 uppercase letter
                                </div>
                                <div class="criteria-item" id="lowercase-criteria">
                                    <i class="bi bi-circle"></i> At least 1 lowercase letter
                                </div>
                                <div class="criteria-item" id="number-criteria">
                                    <i class="bi bi-circle"></i> At least 1 number
                                </div>
                                <div class="criteria-item" id="special-criteria">
                                    <i class="bi bi-circle"></i> At least 1 special character
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                       placeholder="Confirm your new password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                </button>
                            </div>
                            <div id="password-match" class="form-text"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="resetButton">
                            <i class="bi bi-lock-fill me-2"></i> Reset Password
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                        <h3>Unable to Reset Password</h3>
                        <p class="text-muted">The password reset link is invalid or has expired.</p>
                        <a href="forgot_password.php" class="btn btn-primary mt-3 px-4">Request New Link</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="auth-footer">
                Remember your password? <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
            const strengthBar = document.getElementById('password-strength-bar');
            const passwordMatch = document.getElementById('password-match');
            const resetButton = document.getElementById('resetButton');
            const resetForm = document.getElementById('resetPasswordForm');
            
            if (!passwordField) return; // Guard clause if we're not on the reset form
            
            // Criteria elements
            const lengthCriteria = document.getElementById('length-criteria');
            const uppercaseCriteria = document.getElementById('uppercase-criteria');
            const lowercaseCriteria = document.getElementById('lowercase-criteria');
            const numberCriteria = document.getElementById('number-criteria');
            const specialCriteria = document.getElementById('special-criteria');
            
            // Toggle password visibility
            togglePasswordBtn.addEventListener('click', function() {
                togglePasswordVisibility(passwordField, 'toggleIcon');
            });
            
            toggleConfirmPasswordBtn.addEventListener('click', function() {
                togglePasswordVisibility(confirmPasswordField, 'toggleConfirmIcon');
            });
            
            function togglePasswordVisibility(field, iconId) {
                const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                field.setAttribute('type', type);
                
                const icon = document.getElementById(iconId);
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            }
            
            // Password strength checker
            passwordField.addEventListener('input', function() {
                const password = this.value;
                checkPasswordStrength(password);
                checkPasswordMatch();
            });
            
            confirmPasswordField.addEventListener('input', checkPasswordMatch);
            
            // Check if passwords match
            function checkPasswordMatch() {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                
                if (confirmPassword.length === 0) {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'form-text';
                    return;
                }
                
                if (password === confirmPassword) {
                    passwordMatch.textContent = 'Passwords match';
                    passwordMatch.className = 'form-text text-success';
                } else {
                    passwordMatch.textContent = 'Passwords do not match';
                    passwordMatch.className = 'form-text text-danger';
                }
            }
            
            // Check password strength and update UI
            function checkPasswordStrength(password) {
                let strength = 0;
                let meetsAllCriteria = true;
                
                // Check length
                const meetsLength = password.length >= 8;
                updateCriteriaUI(lengthCriteria, meetsLength);
                if (meetsLength) strength += 1;
                else meetsAllCriteria = false;
                
                // Check lowercase
                const meetsLowercase = /[a-z]/.test(password);
                updateCriteriaUI(lowercaseCriteria, meetsLowercase);
                if (meetsLowercase) strength += 1;
                else meetsAllCriteria = false;
                
                // Check uppercase
                const meetsUppercase = /[A-Z]/.test(password);
                updateCriteriaUI(uppercaseCriteria, meetsUppercase);
                if (meetsUppercase) strength += 1;
                else meetsAllCriteria = false;
                
                // Check numbers
                const meetsNumber = /[0-9]/.test(password);
                updateCriteriaUI(numberCriteria, meetsNumber);
                if (meetsNumber) strength += 1;
                else meetsAllCriteria = false;
                
                // Check special characters
                const meetsSpecial = /[^a-zA-Z0-9]/.test(password);
                updateCriteriaUI(specialCriteria, meetsSpecial);
                if (meetsSpecial) strength += 1;
                else meetsAllCriteria = false;
                
                // Update strength bar
                const width = (strength / 5) * 100;
                strengthBar.style.width = width + '%';
                
                // Update color
                if (strength <= 2) {
                    strengthBar.style.backgroundColor = '#f72585'; // Weak
                } else if (strength <= 4) {
                    strengthBar.style.backgroundColor = '#4cc9f0'; // Medium
                } else {
                    strengthBar.style.backgroundColor = '#4ad66d'; // Strong
                }
                
                // Enable/disable reset button based on criteria
                resetButton.disabled = !meetsAllCriteria;
            }
            
            function updateCriteriaUI(element, isMet) {
                if (isMet) {
                    element.classList.add('met');
                    element.querySelector('i').className = 'bi bi-check-circle-fill';
                } else {
                    element.classList.remove('met');
                    element.querySelector('i').className = 'bi bi-circle';
                }
            }
            
            // Disable form submission if passwords don't match
            resetForm.addEventListener('submit', function(e) {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    passwordMatch.textContent = 'Passwords do not match';
                    passwordMatch.className = 'form-text text-danger';
                    confirmPasswordField.focus();
                }
            });
        });
    </script>
</body>
</html>