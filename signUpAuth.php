<?php

require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();
include 'configs/dbconnection.php';

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

header('Content-Type: application/json'); 
$message = "";
$alertType = "";
$redirect = false;
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = trim($_POST['student'] ?? '');
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $department = htmlspecialchars(trim($_POST['department'] ?? ''), ENT_QUOTES, 'UTF-8');
    $dob = $_POST['dob'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', $_POST['contact'] ?? ''); // Remove non-numeric chars

    if (!$studentID || !$name || !$email || !$password || !$department || !$dob || !$phone) {
        $message = "❌ All fields are required.";
        $alertType = "danger";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO students (studentID, name, email, password, department, dateOfBirth, contactNumber) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $studentID, $name, $email, $hashedPassword, $department, $dob, $phone);
            
            if ($stmt->execute()) {
                sendEmailConfirmation($email, $name, $studentID, $department, $phone);
                sendSMSConfirmation($phone, $name);
                $message = "🎉 Registration successful! A confirmation email and SMS have been sent.";
                $alertType = "success";
                $redirect = true;
                $redirectUrl = 'login.php'; // ✅ Set redirect URL
            } else {
                error_log("Database Error: " . $stmt->error);
                $message = "❌ Error processing your registration.";
                $alertType = "danger";
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage());
            $message = "❌ Something went wrong. Please try again.";
            $alertType = "danger";
        }
    }
    $conn->close();
}

// ✅ Ensure a JSON response is sent
echo json_encode([
    "status" => $alertType === "success" ? "success" : "error",
    "message" => $message,
    "redirect_url" => $redirect ? $redirectUrl : null
]);
exit(); // ✅ Make sure no HTML is output after this

function sendEmailConfirmation($email, $name, $studentID, $department, $phone) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_EMAIL']; 
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        $mail->setFrom('smartvote@outlook.com', 'SmartVote EMS');
        $mail->addAddress($email, $name);
        
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to SmartVote EMS';
        $mail->Body = "<h2>Hello, $name</h2><p>Your registration is complete!</p>";
        $mail->AltBody = "Hello $name, your registration is successful!";
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
    }
}

function sendSMSConfirmation($phone, $name) {
    $apiKey = $_ENV['ARKESKEL_API_KEY'];
    $smsMessage = "Hello $name, your registration is successful!";
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
    
    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
    } elseif ($httpCode !== 200) {
        error_log("API Error: HTTP Code $httpCode, Response: " . $response);
    }
    
    curl_close($ch);
}
?>
