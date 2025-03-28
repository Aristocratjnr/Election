<?php
session_start();
include 'configs/dbconnection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

$message = "";
$alertType = "";
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = $_POST['student'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $department = $_POST['department'] ?? '';
    $phone = $_POST['contact'] ?? '';

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO students (studentID, name, email, password, department, contactNumber) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("ssssss", $studentID, $name, $email, $hashedPassword, $department, $phone);

        if ($stmt->execute()) {
            // Email sending
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ayimobuobi@gmail.com'; // Your Gmail
                $mail->Password = 'qesdrupqvtfldoxw';     // App password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                // Sender & Recipient
                $mail->setFrom('smartvote@gmail.com', 'SmartVote EMS');
                $mail->addAddress($email, $name);

                // Email Content
                $mail->isHTML(true);
                $mail->Subject = 'Welcome to SmartVote EMS';
                $mail->Body    = "
                    <h2 style='color:#2d6a4f;'>Hello, $name 👋</h2>
                    <p>Thank you for registering with the <code>SmartVote EMS</code>.</p>
                    <p><code>Your Details:</code></p>
                    <ul>
                        <li><strong>Student ID:</strong> $studentID</li>
                        <li><strong>Email:</strong> $email</li>
                        <li><strong>Department:</strong> $department</li>
                        <li><strong>Phone:</strong> $phone</li>
                    </ul>
                    <p>You can now <a href='http://localhost/election/login.php'>log in</a> and participate in voting.</p>
                    <br>
                    <p style='color:#6c757d;'>If you have any issues, please contact support.</p>
                    <p><strong>Best regards,</strong><br>Student Voting System</p>
                ";
                $mail->AltBody = "Hello $name,\n\nYour registration was successful.\nStudent ID: $studentID\nEmail: $email\nLogin here: http://yourdomain.com/login.php\n\n- Student Voting System";

                $mail->send();
            } catch (Exception $e) {
                error_log("Email not sent: " . $mail->ErrorInfo);
            }

            $message = "🎉 Registration successful! A confirmation email has been sent.";
            $alertType = "success";
            $redirect = true;
        } else {
            $message = "❌ Error: " . $stmt->error;
            $alertType = "danger";
        }

        $stmt->close();
    } else {
        $message = "❌ Statement failed to prepare.";
        $alertType = "danger";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<?php if (!empty($message)): ?>
<!-- Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header bg-<?= $alertType === 'success' ? 'success' : 'danger' ?> text-white">
        <h5 class="modal-title" id="feedbackModalLabel">
            <?= $alertType === 'success' ? 'Success' : 'Error' ?>
        </h5>
      </div>
      <div class="modal-body">
        <p><?= $message ?></p>
        <?php if ($redirect): ?>
            <p class="text-muted">Redirecting to login page...</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
    const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    feedbackModal.show();

    <?php if ($redirect): ?>
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 3000);
    <?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>
