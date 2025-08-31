<?php
/**
 * Enhanced Email Notification Test Script
 * Tests IP detection, browser parsing, and location services
 */

// Include required files
require_once 'configs/dbconnection.php';
require_once 'includes/email_utils.php';

echo "<h1>🔍 Enhanced Email Notification System Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .code { background: #e8e8e8; padding: 10px; margin: 5px 0; border-radius: 3px; font-family: monospace; }
</style>";

// Test 1: IP Address Detection
echo "<div class='test-section'>";
echo "<h2>🌐 Test 1: IP Address Detection</h2>";

$realIP = getRealIPAddress();
echo "<div class='code'>";
echo "Real IP Address: <span class='info'>{$realIP}</span><br>";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'Not set') . "<br>";
echo "HTTP_CLIENT_IP: " . ($_SERVER['HTTP_CLIENT_IP'] ?? 'Not set') . "<br>";
echo "HTTP_X_FORWARDED_FOR: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Not set') . "<br>";
echo "HTTP_CF_CONNECTING_IP: " . ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'Not set') . "<br>";
echo "</div>";

if ($realIP !== 'Unknown') {
    echo "<div class='success'>✅ IP address detected successfully</div>";
} else {
    echo "<div class='error'>❌ Failed to detect IP address</div>";
}
echo "</div>";

// Test 2: Browser Detection
echo "<div class='test-section'>";
echo "<h2>🖥️ Test 2: Browser & Device Detection</h2>";

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
$browserInfo = getBrowserInfo($userAgent);

echo "<div class='code'>";
echo "User Agent: <br><small>{$userAgent}</small><br><br>";
echo "Parsed Browser Info: <span class='info'>{$browserInfo}</span>";
echo "</div>";

if ($browserInfo !== 'Unknown Browser on Unknown OS (Desktop)') {
    echo "<div class='success'>✅ Browser information parsed successfully</div>";
} else {
    echo "<div class='error'>❌ Failed to parse browser information</div>";
}
echo "</div>";

// Test 3: Location Detection
echo "<div class='test-section'>";
echo "<h2>📍 Test 3: Location Detection</h2>";

$locationInfo = getLocationInfo($realIP);
echo "<div class='code'>";
echo "IP: {$realIP}<br>";
echo "Location: <span class='info'>{$locationInfo}</span>";
echo "</div>";

if (strpos($locationInfo, 'lookup failed') === false) {
    echo "<div class='success'>✅ Location information retrieved</div>";
} else {
    echo "<div class='error'>❌ Location lookup failed</div>";
}
echo "</div>";

// Test 4: Database Table Update
echo "<div class='test-section'>";
echo "<h2>🗄️ Test 4: Database Table Structure</h2>";

try {
    // Check if table exists and has required columns
    $result = $conn->query("DESCRIBE login_logs");
    if ($result) {
        echo "<div class='code'>";
        echo "Table structure:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})<br>";
        }
        echo "</div>";
        
        // Check for new columns
        $hasLocationInfo = false;
        $hasBrowserInfo = false;
        
        $result = $conn->query("DESCRIBE login_logs");
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'location_info') $hasLocationInfo = true;
            if ($row['Field'] === 'browser_info') $hasBrowserInfo = true;
        }
        
        if ($hasLocationInfo && $hasBrowserInfo) {
            echo "<div class='success'>✅ Database table has all required columns</div>";
        } else {
            echo "<div class='error'>❌ Missing columns. Run the SQL update script.</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 5: Test Different User Agents
echo "<div class='test-section'>";
echo "<h2>🧪 Test 5: Browser Detection with Different User Agents</h2>";

$testUserAgents = [
    'Chrome Windows' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Firefox Mac' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/121.0',
    'Safari Mac' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
    'Edge Windows' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
    'Mobile Chrome' => 'Mozilla/5.0 (Linux; Android 12; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
    'iPhone Safari' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1'
];

echo "<div class='code'>";
foreach ($testUserAgents as $name => $ua) {
    $parsed = getBrowserInfo($ua);
    echo "<strong>{$name}:</strong><br>";
    echo "<small>{$ua}</small><br>";
    echo "→ {$parsed}<br><br>";
}
echo "</div>";

echo "<div class='success'>✅ Browser detection working with various user agents</div>";
echo "</div>";

// Test 6: Send Test Email with Enhanced Info
echo "<div class='test-section'>";
echo "<h2>📧 Test 6: Send Enhanced Email Notification</h2>";

$testEmail = 'ayimobuobi@gmail.com'; // Admin email from database
$testName = 'Enhanced Test User';
$testStudentID = 'TEST999';

try {
    $result = sendLoginNotification(
        $testEmail, 
        $testName, 
        $testStudentID, 
        'student',
        date('Y-m-d H:i:s'),
        $realIP,
        $userAgent
    );
    
    if ($result) {
        echo "<div class='success'>✅ Enhanced email notification sent successfully!</div>";
        echo "<div class='code'>";
        echo "Email sent to: {$testEmail}<br>";
        echo "IP: {$realIP}<br>";
        echo "Browser: {$browserInfo}<br>";
        echo "Location: {$locationInfo}<br>";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Failed to send email notification</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Email test failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 7: Test Database Logging
echo "<div class='test-section'>";
echo "<h2>📝 Test 7: Database Activity Logging</h2>";

try {
    $result = logLoginActivity($conn, $testStudentID, 'student', $realIP, $userAgent, $browserInfo, $locationInfo);
    
    if ($result) {
        echo "<div class='success'>✅ Login activity logged successfully!</div>";
        
        // Show the logged entry
        $stmt = $conn->prepare("SELECT * FROM login_logs WHERE studentID = ? ORDER BY login_time DESC LIMIT 1");
        $stmt->bind_param("s", $testStudentID);
        $stmt->execute();
        $logEntry = $stmt->get_result()->fetch_assoc();
        
        if ($logEntry) {
            echo "<div class='code'>";
            echo "Latest log entry:<br>";
            foreach ($logEntry as $key => $value) {
                echo "- {$key}: {$value}<br>";
            }
            echo "</div>";
        }
    } else {
        echo "<div class='error'>❌ Failed to log activity</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Logging test failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<h2>🎉 Enhanced Email Notification System Test Complete!</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Test with actual user logins through the portal</li>";
echo "<li>Check email inbox for detailed notifications</li>";
echo "<li>Monitor login_logs table for detailed activity tracking</li>";
echo "<li>Consider adding IP geolocation API key for better location accuracy</li>";
echo "</ul>";

$conn->close();
?>
