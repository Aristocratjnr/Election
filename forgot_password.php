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
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT studentID FROM Students WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_id = $user['studentID'];
                
                // Generate token
                $token = bin2hex(random_bytes(32)); // 64-character random string
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
                
                // Delete any existing tokens for this user
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                
                // Store new token
                $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $user_id, $token, $expires_at);
                $stmt->execute();
                
                // Send email with reset link
                $reset_link = "http://localhost/election/reset-password.php?token=$token";
                $subject = "Password Reset Request";
                $message = "Hello,\n\nYou requested a password reset. Click the link below to reset your password:\n\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.";
                $headers = "From: no-reply@yourdomain.com";
                
                if (mail($email, $subject, $message, $headers)) {
                    $success = 'Password reset link has been sent to your email';
                } else {
                    $error = 'Failed to send email. Please try again later.';
                }
            } else {
                $error = 'No account found with that email address';
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
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
        /* Use the same styles as your reset-password.php */
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
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .auth-footer {
            text-align: center;
            padding: 1rem 2rem;
            border-top: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Forgot Password</h2>
                <p>Enter your email to receive a reset link<i class="bi bi-lock action-icon icon"></i></p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="reset-password.php">
                    <div class="mb-3">
                        <label for="email" class="form-label"><i class="bi bi-inbox action-icon icon"></i>&nbsp;Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Enter your registered email" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-envelope-fill me-2"></i> Send Reset Link
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                Remember your password? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>