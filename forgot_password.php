<?php
session_start();
require 'configs/dbconnection.php';
use PHPMailer\PHPMailer\PHPMailer;
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if email exists in students table
    try {
        $stmt = $conn->prepare("SELECT studentID, email FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        
        if ($student) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
            
            // Store token in password_resets table
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expires);
            $stmt->execute();
            
            // Create reset link
            $resetLink = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]/reset-password.php?token=$token";
            
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   =  $_ENV['SMTP_EMAIL']; 
                $mail->Password   = $_ENV['SMTP_PASSWORD']; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Recipients
                $mail->setFrom('no-reply@smartvote.com', 'SmartVote');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request - SmartVote';
                $mail->Body    = "
                    <h2>Password Reset Request</h2>
                    <p>Hello,</p>
                    <p>You requested a password reset for your SmartVote account.</p>
                    <p><a href='$resetLink' style='background:#7367f0;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
                    <p>Or copy this link: <code>$resetLink</code></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                    <p>Best regards,<br>SmartVote </p>
                ";
                $mail->AltBody = "Password Reset Link: $resetLink";

                $mail->send();
                $success = 'Password reset link has been sent to your email. Check your inbox (and spam folder).';
            } catch (Exception $e) {
                $error = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = 'Email not found in our system.';
        }
    } catch (mysqli_sql_exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-assets-path="assets/" data-template="vertical-menu-template" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Forgot Password - SmartVote</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet" href="assets/vendor/libs/pickr/pickr-themes.css" />
    <link rel="stylesheet" href="assets/vendor/css/core.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css" />
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/vendor/js/template-customizer.js"></script>
    <script src="assets/js/config.js"></script>
    <style>
        .invalid-feedback { display: block; color: #ff3e1d; font-size: 0.875em; margin-top: 0.25rem; }
        .is-invalid { border-color: #ff3e1d !important; }
        .btn-reset { background: #7367f0; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="index.php" class="app-brand-link gap-2">
                                <span class="app-brand-text demo text-heading fw-bold">SmartVote</span>
                            </a>
                        </div>
                        <h4 class="mb-1">Forgot Password? 🔒</h4>
                        <p class="mb-6">Enter your email and we'll send you instructions to reset your password</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        
                        <form id="formAuthentication" class="mb-6" method="POST">
                            <div class="mb-6 form-control-validation">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" autofocus />
                            </div>
                            <button type="submit" class="btn btn-primary d-grid w-100">Send Reset Link</button>
                        </form>
                        <div class="text-center">
                            <a href="login.php" class="d-flex justify-content-center">
                                <i class="icon-base bx bx-chevron-left scaleX-n1-rtl me-1"></i>
                                Back to login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/@algolia/autocomplete-js.js"></script>
    <script src="assets/vendor/libs/pickr/pickr.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/libs/i18n/i18n.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
    document.getElementById('formAuthentication').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        document.getElementById('email').classList.remove('is-invalid');
        const existingError = document.querySelector('.invalid-feedback');
        if (existingError) existingError.remove();
        
        if (!email) {
            showError('email', 'Email is required');
            return;
        }
        
        if (!emailRegex.test(email)) {
            showError('email', 'Please enter a valid email address');
            return;
        }
        
        this.submit();
    });

    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    </script>
</body>
</html>