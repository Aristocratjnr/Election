<?php
/**
 * Email Utility Functions for SmartVote
 * Handles various email notifications
 */

require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load environment variables if not already loaded
if (!isset($_ENV['SMTP_EMAIL'])) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

/**
 * Send login notification email
 * @param string $email User's email address
 * @param string $name User's name
 * @param string $studentID User's student ID
 * @param string $userRole User's role (student/admin)
 * @param string $loginTime Login timestamp
 * @param string $ipAddress User's IP address
 * @param string $userAgent User's browser/device info
 * @return bool Success status
 */
function sendLoginNotification($email, $name, $studentID, $userRole = 'student', $loginTime = null, $ipAddress = null, $userAgent = null) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_EMAIL'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'tls';
        $mail->Port = $_ENV['SMTP_PORT'] ?? 587;
        
        // Recipients
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@smartvote.com', 'SmartVote EMS');
        $mail->addAddress($email, $name);
        
        // Set default values
        $loginTime = $loginTime ?: date('Y-m-d H:i:s');
        $ipAddress = $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $userAgent = $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
        
        // Parse user agent for better display
        $browserInfo = getBrowserInfo($userAgent);
        $locationInfo = getLocationInfo($ipAddress);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'SmartVote Login Alert - ' . ucfirst($userRole) . ' Portal Access';
        
        // Professional HTML email template
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Notification</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background-color: #f4f4f4; 
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background: white; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1); 
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
            font-weight: 600; 
        }
        .content { 
            padding: 30px; 
        }
        .alert-box { 
            background: #e8f4fd; 
            border-left: 4px solid #2196F3; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 4px; 
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            background: #f8f9fa; 
            border-radius: 8px; 
            overflow: hidden; 
        }
        .info-table th, .info-table td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #dee2e6; 
        }
        .info-table th { 
            background: #343a40; 
            color: white; 
            font-weight: 600; 
            width: 30%; 
        }
        .info-table tr:last-child td { 
            border-bottom: none; 
        }
        .security-note { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            color: #856404; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 20px 0; 
        }
        .footer { 
            background: #f8f9fa; 
            padding: 20px; 
            text-align: center; 
            font-size: 12px; 
            color: #6c757d; 
            border-top: 1px solid #dee2e6; 
        }
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            background: #667eea; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: 600; 
            margin: 10px 0; 
        }
        .status-badge { 
            display: inline-block; 
            padding: 4px 12px; 
            background: #28a745; 
            color: white; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 600; 
        }
        .icon { 
            font-size: 18px; 
            margin-right: 8px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Login Alert</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">SmartVote Election Management System</p>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <strong>✅ Successful Login Detected</strong><br>
                Your SmartVote account was accessed successfully.
            </div>
            
            <p>Hello <strong>{$name}</strong>,</p>
            
            <p>We're writing to inform you that your SmartVote account was accessed on <strong>{$loginTime}</strong>.</p>
            
            <table class="info-table">
                <tr>
                    <th>👤 Account</th>
                    <td>{$studentID} ({$name})</td>
                </tr>
                <tr>
                    <th>🎭 Role</th>
                    <td><span class="status-badge">{$userRole}</span></td>
                </tr>
                <tr>
                    <th>⏰ Login Time</th>
                    <td>{$loginTime}</td>
                </tr>
                <tr>
                    <th>🌐 IP Address</th>
                    <td>{$ipAddress}</td>
                </tr>
                <tr>
                    <th>📍 Location</th>
                    <td>{$locationInfo}</td>
                </tr>
                <tr>
                    <th>💻 Device/Browser</th>
                    <td>{$browserInfo}</td>
                </tr>
            </table>
            
            <div class="security-note">
                <strong>🛡️ Security Notice:</strong><br>
                If this wasn't you, please contact the system administrator immediately and change your password.
                <br><br>
                <a href="{$_ENV['BASE_URL']}/forgot_password.php" class="btn">Change Password</a>
            </div>
            
            <p>For your security, here are some tips:</p>
            <ul>
                <li>Always log out when using shared computers</li>
                <li>Use strong, unique passwords</li>
                <li>Don't share your login credentials</li>
                <li>Report suspicious activity immediately</li>
            </ul>
            
            <p>If you have any questions or concerns, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br><strong>SmartVote- Aristocrajnr</strong></p>
        </div>
        
        <div class="footer">
            <p>This is an automated security notification from SmartVote EMS.<br>
            © 2025 SmartVote. All rights reserved.</p>
            <p>If you did not request this login, please contact support immediately.</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Alternative plain text version
        $mail->AltBody = "SmartVote Login Alert\n\n" .
                        "Hello {$name},\n\n" .
                        "Your SmartVote account was accessed successfully.\n\n" .
                        "Login Details:\n" .
                        "- Account: {$studentID} ({$name})\n" .
                        "- Role: {$userRole}\n" .
                        "- Time: {$loginTime}\n" .
                        "- IP Address: {$ipAddress}\n" .
                        "- Device: {$browserInfo}\n\n" .
                        "If this wasn't you, please contact support immediately.\n\n" .
                        "Best regards,\nSmartVote Team";
        
        $mail->send();
        error_log("Login notification email sent successfully to: " . $email);
        return true;
        
    } catch (Exception $e) {
        error_log("Login notification email failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Get browser and device information from user agent
 * @param string $userAgent
 * @return string
 */
function getBrowserInfo($userAgent) {
    $browser = 'Unknown Browser';
    $os = 'Unknown OS';
    
    // Detect browser
    if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
        $browser = 'Google Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $browser = 'Mozilla Firefox';
    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
        $browser = 'Safari';
    } elseif (strpos($userAgent, 'Edg') !== false) {
        $browser = 'Microsoft Edge';
    } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
        $browser = 'Opera';
    }
    
    // Detect OS
    if (strpos($userAgent, 'Windows NT 10') !== false) {
        $os = 'Windows 10/11';
    } elseif (strpos($userAgent, 'Windows NT') !== false) {
        $os = 'Windows';
    } elseif (strpos($userAgent, 'Macintosh') !== false) {
        $os = 'macOS';
    } elseif (strpos($userAgent, 'Linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($userAgent, 'Android') !== false) {
        $os = 'Android';
    } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
        $os = 'iOS';
    }
    
    // Detect if mobile
    $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4));
    
    $deviceType = $isMobile ? 'Mobile Device' : 'Desktop';
    
    return "{$browser} on {$os} ({$deviceType})";
}

/**
 * Get location information from IP address
 * @param string $ipAddress
 * @return string
 */
function getLocationInfo($ipAddress) {
    // For local development
    if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
        return 'Local Network';
    }
    
    // You can integrate with IP geolocation services here
    // For now, return basic info
    return $ipAddress . ' (External)';
}

/**
 * Log login activity to database
 * @param object $conn Database connection
 * @param int $studentID
 * @param string $userRole
 * @param string $ipAddress
 * @param string $userAgent
 * @return bool
 */
function logLoginActivity($conn, $studentID, $userRole, $ipAddress, $userAgent) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO login_logs (studentID, user_role, ip_address, user_agent, login_time) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssss", $studentID, $userRole, $ipAddress, $userAgent);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Failed to log login activity: " . $e->getMessage());
        return false;
    }
}
?>
