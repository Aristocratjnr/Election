<?php
/**
 * Election Reminder Mailer
 * 
 * This script sends email reminders to students a day before an election.
 * It should be run via CRON job once per day.
 * 
 * Example CRON configuration (run daily at 8:00 AM):
 * 0 8 * * * php /path/to/election_reminder_mailer.php
 */

require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set proper error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include database connection
require 'configs/dbconnection.php';

// Set correct timezone
date_default_timezone_set('Africa/Accra');

// Log function to keep track of script execution
function logMessage($message, $type = 'INFO') {
    $logFile = __DIR__ . '/logs/election_reminders_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message" . PHP_EOL;
    
    // Write to log file
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also output to console if script is run manually
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

// Start execution
logMessage("Starting election reminder process");

try {
    // Find elections that are starting tomorrow
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $tomorrowStart = $tomorrow . ' 00:00:00';
    $tomorrowEnd = $tomorrow . ' 23:59:59';
    
    logMessage("Looking for elections between $tomorrowStart and $tomorrowEnd");
    
    $query = "SELECT * FROM elections 
              WHERE status = 'Scheduled' 
              AND startDate BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $tomorrowStart, $tomorrowEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        logMessage("No elections scheduled for tomorrow");
        exit(0);
    }
    
    // Process each election
    while ($election = $result->fetch_assoc()) {
        $electionID = $election['electionID'];
        $electionName = $election['name'];
        $startDate = date('F j, Y', strtotime($election['startDate']));
        $startTime = date('h:i A', strtotime($election['startDate']));
        $endDate = date('F j, Y', strtotime($election['endDate']));
        
        logMessage("Processing election: $electionName (ID: $electionID), starting on $startDate at $startTime");
        
        // Get all active students
        $studentQuery = "SELECT * FROM students WHERE status = 'Active'";
        $studentResult = $conn->query($studentQuery);
        
        if ($studentResult->num_rows === 0) {
            logMessage("No active students found", "WARNING");
            continue;
        }
        
        $successCount = 0;
        $failureCount = 0;
        
        // Send reminder to each student
        while ($student = $studentResult->fetch_assoc()) {
            $studentID = $student['studentID'];
            $studentName = $student['name'];
            $studentEmail = $student['email'];
            $department = $student['department'];
            
            logMessage("Sending reminder to $studentName ($studentEmail)");
            
            if (sendReminderEmail($studentEmail, $studentName, $electionName, $startDate, $startTime, $endDate, $department)) {
                $successCount++;
                
                // Record notification in database
                $title = "Election Reminder";
                $message = "Remember to vote in the $electionName election starting tomorrow ($startDate)";
                
                $notifyStmt = $conn->prepare("
                    INSERT INTO notifications 
                    (user_id, user_type, title, message, type, related_election, is_read, created_at)
                    VALUES (?, 'student', ?, ?, 'reminder', ?, 0, NOW())
                ");
                
                $notifyStmt->bind_param(
                    "issi",
                    $studentID,
                    $title,
                    $message,
                    $electionID
                );
                
                $notifyStmt->execute();
                $notifyStmt->close();
            } else {
                $failureCount++;
            }
        }
        
        logMessage("Completed sending reminders for election $electionName. Successful: $successCount, Failed: $failureCount");
    }
    
    logMessage("Election reminder process completed successfully");
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage(), "ERROR");
    logMessage("Stack trace: " . $e->getTraceAsString(), "ERROR");
    exit(1);
}

/**
 * Send a reminder email to a student
 * 
 * @param string $email Student's email address
 * @param string $name Student's name
 * @param string $electionName Name of the election
 * @param string $startDate Formatted start date
 * @param string $startTime Formatted start time
 * @param string $endDate Formatted end date
 * @param string $department Student's department
 * @return bool True if email was sent successfully, false otherwise
 */
function sendReminderEmail($email, $name, $electionName, $startDate, $startTime, $endDate, $department) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_EMAIL'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = intval($_ENV['SMTP_PORT']);
        
        // Enable debug output when running from CLI
        if (php_sapi_name() === 'cli') {
            $mail->SMTPDebug = 2;
        }
        
        // Recipients
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'SmartVote EMS');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "REMINDER: $electionName Election Tomorrow";
        
        // Generate voter portal URL
        $baseUrl = isset($_ENV['BASE_URL']) ? $_ENV['BASE_URL'] : 'https://smartvote.42web.io';
        $voteUrl = "$baseUrl/student.php";
        
        // Professional HTML email template
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Reminder</title>
    <style>
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; background-color: #f9f9f9; border-radius: 0 0 8px 8px; }
        .footer { padding: 10px; text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
        .details { margin: 20px 0; padding: 15px; border-left: 4px solid #4361ee; background-color: #fff; }
        .detail-item { margin-bottom: 10px; }
        .button { 
            display: inline-block; padding: 12px 24px; 
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white; 
            text-decoration: none; border-radius: 50px; 
            font-weight: bold;
            margin-top: 15px;
        }
        .reminder-box {
            background-color: #fff9db;
            border-left: 4px solid #ffab00;
            padding: 15px;
            margin: 20px 0;
        }
        .countdown {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #4361ee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Election Reminder</h1>
        </div>
        
        <div class="content">
            <p>Dear $name,</p>
            
            <div class="reminder-box">
                <p><strong>IMPORTANT REMINDER:</strong> The election "$electionName" begins tomorrow!</p>
            </div>
            
            <p>This is a friendly reminder that the <strong>$electionName</strong> election will begin tomorrow. Your participation is important for the future of our institution.</p>
            
            <div class="details">
                <div class="detail-item"><strong>Election:</strong> $electionName</div>
                <div class="detail-item"><strong>Start Date:</strong> $startDate at $startTime</div>
                <div class="detail-item"><strong>End Date:</strong> $endDate</div>
                <div class="detail-item"><strong>Your Department:</strong> $department</div>
            </div>
            
            <div class="countdown">
                STARTS IN 24 HOURS
            </div>
            
            <p>Please make sure to cast your vote through the SmartVote system. Remember, your vote matters!</p>
            
            <p style="text-align: center;">
                <a href="$voteUrl" class="button">Access Voting Portal</a>
            </p>
            
            <p>If you have any questions about the voting process, please contact the election committee.</p>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 SmartVote EMS. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->AltBody = "Dear $name,\n\nThis is a reminder that the $electionName election will begin tomorrow ($startDate at $startTime).\n\nPlease make sure to cast your vote through the SmartVote system.\n\nLogin at $voteUrl";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        logMessage("Email Error for $email: " . $mail->ErrorInfo, "ERROR");
        return false;
    }
}