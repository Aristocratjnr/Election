<?php

require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();
include 'configs/dbconnection.php';

// Set headers for JSON response
header('Content-Type: application/json'); 

// Initialize response variables
$response = [
    'status' => 'error',
    'message' => '',
    'redirect_url' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $studentID = trim($_POST['student'] ?? '');
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $department = htmlspecialchars(trim($_POST['department'] ?? ''), ENT_QUOTES, 'UTF-8');
    $dob = $_POST['dob'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', $_POST['contact'] ?? '');

    // Validate required fields
    if (empty($studentID) || empty($name) || !$email || empty($password) || 
        empty($department) || empty($dob) || empty($phone)) {
        $response['message'] = "❌ All fields are required.";
        echo json_encode($response);
        exit();
    }

    // Check if student already exists
    $checkStmt = $conn->prepare("SELECT studentID FROM students WHERE studentID = ? OR email = ?");
    $checkStmt->bind_param("ss", $studentID, $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $response['message'] = "❌ Student ID or Email already exists";
        echo json_encode($response);
        exit();
    }
    $checkStmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO students (studentID, name, email, password, department, dateOfBirth, contactNumber) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $studentID, $name, $email, $hashedPassword, $department, $dob, $phone);
        
        if ($stmt->execute()) {
            // Send notifications (async to speed up response)
            register_shutdown_function(function() use ($email, $name, $studentID, $department, $phone) {
                sendEmailConfirmation($email, $name, $studentID, $department, $phone);
                sendSMSConfirmation($phone, $name);
            });
            
            $response['status'] = 'success';
            $response['message'] = "🎉 Registration successful! Confirmation sent to your email and phone.";
            $response['redirect_url'] = 'login.php';
        } else {
            throw new Exception("Database insertion failed");
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Registration Error: " . $e->getMessage());
        $response['message'] = "❌ Registration failed. Please try again.";
    }
    $conn->close();
} else {
    $response['message'] = "❌ Invalid request method";
}

echo json_encode($response);
exit();

function sendEmailConfirmation($email, $name, $studentID, $department, $phone) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_EMAIL'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'ssl';
        $mail->Port = $_ENV['SMTP_PORT'] ?? 465;
        
        // Recipients
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@smartvote.com', 'SmartVote EMS');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to SmartVote EMS';
        
        // Professional HTML email template
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation</title>
    <style>
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4a6baf; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 10px; text-align: center; font-size: 12px; color: #777; }
        .details { margin: 20px 0; }
        .detail-item { margin-bottom: 10px; }
        .button { 
            display: inline-block; padding: 10px 20px; 
            background-color: #4a6baf; color: white; 
            text-decoration: none; border-radius: 4px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to SmartVote EMS</h1>
        </div>
        
        <div class="content">
            <p>Dear $name,</p>
            <p>Thank you for registering with SmartVote Election Management System. Your account has been successfully created.</p>
            
            <div class="details">
                <div class="detail-item"><strong>Student ID:</strong> $studentID</div>
                <div class="detail-item"><strong>Department:</strong> $department</div>
                <div class="detail-item"><strong>Registered Email:</strong> $email</div>
                <div class="detail-item"><strong>Phone Number:</strong> $phone</div>
            </div>
            
            <p>You can now login to your account using your student ID and password.</p>
            
            <p style="text-align: center;">
                <a href="https://yourdomain.com/login.php" class="button">Login to Your Account</a>
            </p>
            
            <p>If you did not request this registration, please contact our support team immediately.</p>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 SmartVote EMS. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->AltBody = "Dear $name,\n\nThank you for registering with SmartVote EMS.\n\nStudent ID: $studentID\nDepartment: $department\nEmail: $email\nPhone: $phone\n\nYou can now login at https://yourdomain.com/login.php";
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
    }
}

function sendSMSConfirmation($phone, $name) {
    $apiKey = $_ENV['ARKESKEL_API_KEY'] ?? '';
    if (empty($apiKey)) return;
    
    $smsMessage = "Hello $name, your SmartVote registration is successful! Login at https://yourdomain.com/login.php";
    
    $smsData = [
        "sender" => "SmartVote",
        "message" => $smsMessage,
        "recipients" => [$phone]
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://sms.arkesel.com/api/v2/sms/send",
        CURLOPT_HTTPHEADER => [
            "api-key: $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($smsData),
        CURLOPT_TIMEOUT => 5 // 5 second timeout
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("SMS cURL Error: " . curl_error($ch));
    }
    curl_close($ch);
}
