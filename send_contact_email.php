<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['fullname', 'email', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst($field) . ' is required';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation errors', 'errors' => $errors]);
    exit;
}

// Sanitize input data
$fullname = htmlspecialchars(trim($_POST['fullname']));
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($_POST['message']));

// Validate email
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Email configuration
$to = 'ayimobuobi@gmail.com';
$subject = 'New Contact Inquiry from SmartVote - ' . $fullname;

// Create email body
$email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>New Contact Inquiry</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #696cff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #696cff; }
        .value { margin-top: 5px; padding: 10px; background: white; border-radius: 3px; border-left: 3px solid #696cff; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 New Contact Inquiry - SmartVote</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Full Name:</div>
                <div class='value'>{$fullname}</div>
            </div>
            
            <div class='field'>
                <div class='label'>📧 Email Address:</div>
                <div class='value'>{$email}</div>
            </div>
            
            <div class='field'>
                <div class='label'>💬 Message:</div>
                <div class='value'>" . nl2br($message) . "</div>
            </div>
            
            <div class='field'>
                <div class='label'>🕐 Received At:</div>
                <div class='value'>" . date('Y-m-d H:i:s') . "</div>
            </div>
            
            <div class='field'>
                <div class='label'>🌐 Source:</div>
                <div class='value'>SmartVote Contact Form</div>
            </div>
        </div>
        <div class='footer'>
            <p>This email was sent from the SmartVote contact form.</p>
            <p>Reply directly to this email to respond to the inquiry.</p>
        </div>
    </div>
</body>
</html>
";

// Email headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: SmartVote Contact Form <noreply@smartvote.com>" . "\r\n";
$headers .= "Reply-To: {$email}" . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Additional headers for better deliverability
$headers .= "X-Priority: 3" . "\r\n";
$headers .= "X-MSMail-Priority: Normal" . "\r\n";
$headers .= "X-Mailer: SmartVote Contact Form" . "\r\n";

try {
    // Send email
    $mail_sent = mail($to, $subject, $email_body, $headers);
    
    if ($mail_sent) {
        // Log successful submission (optional)
        $log_entry = date('Y-m-d H:i:s') . " - Contact form submission from: {$email} ({$fullname})" . PHP_EOL;
        file_put_contents('logs/contact_submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Email sent successfully'
        ]);
    } else {
        throw new Exception('Failed to send email');
    }
    
} catch (Exception $e) {
    // Log error
    $error_log = date('Y-m-d H:i:s') . " - Contact form error: " . $e->getMessage() . " from: {$email}" . PHP_EOL;
    file_put_contents('logs/contact_errors.log', $error_log, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email. Please try again later.'
    ]);
}
?>
