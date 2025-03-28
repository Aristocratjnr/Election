<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


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

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO students (studentID, name, email, password, department, contactNumber) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("ssssss", $studentID, $name, $email, $hashedPassword, $department, $phone);

        if ($stmt->execute()) {
            // PHPMailer - Send Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_EMAIL']; 
                $mail->Password = $_ENV['SMTP_PASSWORD'];     
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('smartvote@gmail.com', 'SmartVote EMS');
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to SmartVote EMS';
                $mail->Body = "
              <div style='font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; border-radius: 10px; color: #212529; max-width: 600px; margin: auto;'>
            < h2 style='color: #2d6a4f;'>Hello, $name 👋</h2>
            <p>Thank you for registering with <strong>SmartVote EMS</strong>.</p>

           <div style='background-color: #ffffff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 15px;'>
            <h3 style='margin-top: 0; color: #1b4332;'>Your Registration Details:</h3>
            <ul style='list-style-type: none; padding: 0;'>
                <li><strong>Student ID:</strong> $studentID</li>
                <li><strong>Email:</strong> $email</li>
                <li><strong>Department:</strong> $department</li>
                <li><strong>Phone:</strong> $phone</li>
            </ul>
           </div>

          <p style='margin-top: 20px;'>You can now <a href='http://localhost/election/login.php' style='color: #40916c; text-decoration: none; font-weight: bold;'>log in</a> and participate in voting.</p>
        
          <p style='color: #6c757d; font-size: 14px;'>If you have any issues, please contact support.</p>
        
           <p style='margin-top: 30px;'>
            <strong>Best regards,</strong><br>
            The Student Voting System Team
              </p>
               </div>
           <p style='font-size: 12px; color: #6c757d;'>This is an automated message, please do not reply.</p>";

                $mail->AltBody = "Hello $name,\n\nYour registration was successful.\nStudent ID: $studentID\nEmail: $email\nLogin here: http://yourdomain.com/login.php\n\n- Student Voting System";

                $mail->send();

                // ✅ Arkesel SMS API
                $apiKey = $_ENV['ARKESKEL_API_KEY']; // 
                $smsMessage = "Hello $name, your SmartVote EMS registration is successful. Log in to vote now!";
                $smsData = [
                    "sender" => "SmartVote",
                    "message" => $smsMessage,
                    "recipients" => [$phone]
                ];

                $ch = curl_init("https://sms.arkesel.com/api/v2/sms/send");
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "api-key: $apiKey",
                    "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch) || $httpCode !== 200) {
                    error_log("❌ SMS failed to send: " . curl_error($ch));
                }

                curl_close($ch);
            } catch (Exception $e) {
                error_log("❌ Email error: " . $mail->ErrorInfo);
            }

            $message = "🎉 Registration successful! A confirmation email and SMS have been sent.";
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
