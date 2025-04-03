<?php
// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Database connection
require 'configs/dbconnection.php';

// Initialize variables
$email = '';
$error = '';
$success = '';
$is_sent = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if email exists in database
            $stmt = $conn->prepare("SELECT studentID, name FROM Students WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_id = $user['studentID'];
                $name = $user['name'];
                
                // Delete any existing tokens for this user
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $user_id, $token, $expires);
                $stmt->execute();
                
                // Create reset link
                $reset_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/reset-password.php?token=$token";
                
                // Store in session for demo purposes
                $_SESSION['reset_link'] = $reset_link;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_token'] = $token;
                
                // In production, you would send an actual email here
                // sendPasswordResetEmail($email, $name, $reset_link);
                
                $is_sent = true;
                $success = "We've sent a password reset link to <strong>$email</strong>. Please check your inbox.";
            } else {
                $error = 'No account found with that email address';
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SmartVote</title>
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
        
        .demo-note {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-top: 20px;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
        }
        
        .demo-note h6 {
            color: var(--primary-color);
            font-weight: 600;
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
                <h2>Forgot Password?</h2>
                <p>No worries, we'll help you reset it</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($is_sent): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <h3>Email Sent!</h3>
                        <p class="text-muted"><?php echo $success; ?></p>
                        <p class="text-muted">Didn't receive the email? Check your spam folder or <a href="forgot-password.php">try again</a>.</p>
                        <a href="login.php" class="btn btn-primary mt-3 px-4">Back to Login</a>
                        
                        <!-- Demo only - remove in production -->
                        <?php if (isset($_SESSION['reset_link'])): ?>
                        <div class="demo-note mt-4">
                            <h6>Demo Note</h6>
                            <p>In production, this would send an actual email. For this demo:</p>
                            <p><strong>Reset Link:</strong> <a href="<?php echo $_SESSION['reset_link']; ?>" target="_blank">Click to reset password</a></p>
                            <p class="small text-muted mb-0">Token: <?php echo $_SESSION['reset_token']; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <form method="POST" action="reset-password.php"> 
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" required
                                       placeholder="Enter your registered email">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send-fill me-2"></i> Send Reset Link
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="auth-footer">
                Remember your password? <a href="login.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus email field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.focus();
            }
        });
    </script>
</body>
</html>