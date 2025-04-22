<?php
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_EMAIL'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = intval($_ENV['SMTP_PORT']);

    // Recipients
    $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'SmartVote EMS');
    $mail->addAddress($_ENV['SMTP_EMAIL']); // Send to the same email for testing

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from SmartVote EMS';
    $mail->Body    = 'This is a test email to verify the SMTP configuration is working correctly.';
    $mail->AltBody = 'This is a test email to verify the SMTP configuration is working correctly.';

    $mail->send();
    echo "Test email sent successfully!\n";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}