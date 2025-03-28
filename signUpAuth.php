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
                $mail->Username = $_ENV['SMTP_EMAIL']; // Gmail
                $mail->Password = $_ENV['SMTP_PASSWORD'];     // App password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('smartvote@gmail.com', 'SmartVote EMS');
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to SmartVote EMS';
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>SmartVote EMS - Registration Confirmation</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            line-height: 1.6;
                            background-color: #f4f6f9;
                            margin: 0;
                            padding: 0;
                            color: #333;
                        }
                        .email-container {
                            max-width: 600px;
                            margin: 20px auto;
                            background-color: #ffffff;
                            border-radius: 12px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            overflow: hidden;
                        }
                        .email-header {
                            background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%);
                            color: white;
                            padding: 20px;
                            text-align: center;
                        }
                        .email-header h1 {
                            margin: 0;
                            font-size: 24px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .email-header img {
                            margin-right: 10px;
                            width: 40px;
                            height: 40px;
                        }
                        .email-content {
                            padding: 30px;
                        }
                        .user-details {
                            background-color: #f1f3f5;
                            border-radius: 8px;
                            padding: 20px;
                            margin: 20px 0;
                        }
                        .user-details ul {
                            list-style-type: none;
                            padding: 0;
                            margin: 0;
                        }
                        .user-details li {
                            margin-bottom: 10px;
                            display: flex;
                            align-items: center;
                            border-bottom: 1px solid #e9ecef;
                            padding-bottom: 10px;
                        }
                        .user-details li:last-child {
                            border-bottom: none;
                        }
                        .user-details li strong {
                            margin-right: 10px;
                            min-width: 120px;
                            display: inline-block;
                            color: #2d6a4f;
                            font-weight: 600;
                        }
                        .login-button {
                            display: block;
                            width: 200px;
                            margin: 20px auto;
                            padding: 12px 20px;
                            background-color: #40916c;
                            color: white;
                            text-decoration: none;
                            text-align: center;
                            border-radius: 6px;
                            font-weight: bold;
                            transition: background-color 0.3s ease;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        }
                        .login-button:hover {
                            background-color: #2d6a4f;
                            text-decoration: none;
                        }
                        .email-footer {
                            background-color: #f1f3f5;
                            text-align: center;
                            padding: 15px;
                            font-size: 12px;
                            color: #6c757d;
                        }
                        .support-note {
                            background-color: #e9ecef;
                            border-left: 4px solid #40916c;
                            padding: 10px 15px;
                            margin-top: 20px;
                            font-size: 14px;
                        }
                        @media screen and (max-width: 600px) {
                            .email-container {
                                width: 100%;
                                margin: 0;
                                border-radius: 0;
                            }
                            .email-content {
                                padding: 15px;
                            }
                        }
                    </style>
                    </head>
                    <body>
                        <div class='email-container'>
                            <div class='email-header'>
                                <h1>🗳️ SmartVote EMS</h1>
                            </div>
                            <div class='email-content'>
                                <h2>Hello, $name 👋</h2>
                                <p>Congratulations! Your registration with <strong>SmartVote EMS</strong> is complete.</p>
                                
                                <div class='user-details'>
                                    <h3>Your Registration Details:</h3>
                                    <ul>
                                        <li><strong> Student ID:</strong> $studentID</li>
                                        <li><strong> Email:</strong> $email</li>
                                        <li><strong> Department:</strong> $department</li>
                                        <li><strong> Phone:</strong> $phone</li>
                                    </ul>
                                </div>
                                
                                <a href='http://localhost/election/login.php' class='login-button'>Log In to SmartVote</a>
                                
                                <p>You're now ready to participate in our student voting system. Exercise your democratic right and make your voice heard!</p>
                                
                                <div class='support-note'>
                                    <strong>Need Help?</strong> Contact our support team if you have any questions.
                                </div>
                            </div>
                            <div class='email-footer'>
                                © 2025 SmartVote EMS • Empowering Student Voices
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    $mail->AltBody = "Hello $name,\n\nYour SmartVote EMS registration is successful!\n\nStudent ID: $studentID\nEmail: $email\nDepartment: $department\n\nLogin at: http://localhost/election/login.php\n\n- SmartVote EMS Team";
                                    

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
