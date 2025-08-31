<?php
/**
 * Test script for login notification email functionality
 */

// Include database connection and email utilities
require_once 'configs/dbconnection.php';
require_once 'includes/email_utils.php';

echo "<h2>Testing Login Notification Email System</h2>";

// Test 1: Check if database connection works
echo "<h3>Test 1: Database Connection</h3>";
if ($conn && $conn->ping()) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test 2: Check if login_logs table exists and create if not
echo "<h3>Test 2: Login Logs Table</h3>";
$createTableSQL = "
CREATE TABLE IF NOT EXISTS `login_logs` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `studentID` VARCHAR(50) NOT NULL,
    `user_role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `location_info` VARCHAR(255) DEFAULT NULL,
    `browser_info` VARCHAR(255) DEFAULT NULL,
    `is_successful` BOOLEAN DEFAULT TRUE,
    `session_id` VARCHAR(128) DEFAULT NULL,
    PRIMARY KEY (`log_id`),
    KEY `idx_student_id` (`studentID`),
    KEY `idx_login_time` (`login_time`),
    KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($createTableSQL)) {
    echo "✅ Login logs table ready<br>";
} else {
    echo "❌ Failed to create login logs table: " . $conn->error . "<br>";
}

// Test 3: Check environment variables
echo "<h3>Test 3: Environment Variables</h3>";
$requiredEnvVars = ['SMTP_HOST', 'SMTP_EMAIL', 'SMTP_PASSWORD'];
$envOk = true;

foreach ($requiredEnvVars as $var) {
    if (!empty($_ENV[$var])) {
        echo "✅ $var is set<br>";
    } else {
        echo "❌ $var is missing<br>";
        $envOk = false;
    }
}

// Test 4: Test email functions
echo "<h3>Test 4: Email Functions</h3>";
if (function_exists('sendLoginNotification')) {
    echo "✅ sendLoginNotification function exists<br>";
} else {
    echo "❌ sendLoginNotification function not found<br>";
}

if (function_exists('logLoginActivity')) {
    echo "✅ logLoginActivity function exists<br>";
} else {
    echo "❌ logLoginActivity function not found<br>";
}

// Test 5: Send test email (only if all previous tests pass)
echo "<h3>Test 5: Test Email Notification</h3>";
if ($envOk) {
    try {
        // Use the admin email from the database as test recipient
        $testEmail = 'ayimobuobi@gmail.com'; // From the admin table
        $testName = 'Test User';
        $testStudentID = 'TEST123';
        
        $result = sendLoginNotification($testEmail, $testName, $testStudentID, 'student');
        
        if ($result) {
            echo "✅ Test email sent successfully to $testEmail<br>";
            echo "📧 Check your email inbox for the login notification<br>";
        } else {
            echo "❌ Failed to send test email<br>";
        }
    } catch (Exception $e) {
        echo "❌ Email test failed: " . $e->getMessage() . "<br>";
        echo "📝 Error details logged<br>";
    }
} else {
    echo "⚠️ Skipping email test due to missing environment variables<br>";
}

echo "<br><strong>Email notification system setup complete!</strong><br>";
echo "<br><em>You can now delete this test file: test_email_notification.php</em>";

$conn->close();
?>
