<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Define the root path for includes
define('ROOT_PATH', dirname(dirname(__FILE__)));

// Include required files using absolute paths
require_once ROOT_PATH . '/../configs/dbconnection.php';
require_once ROOT_PATH . '/../configs/session.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

try {
    // Validate session
    if (!isset($_SESSION['login_id'])) {
        throw new Exception('Unauthorized: Session expired or invalid', 401);
    }

    // Get the notification ID from POST data
    $notificationId = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
    
    if ($notificationId <= 0) {
        throw new Exception('Invalid notification ID');
    }

    // Get user info from session
    $userID = (int)$_SESSION['login_id'];
    $userRole = $_SESSION['role'] ?? 'student';
    
    // Update notification as read
    $query = "UPDATE notifications 
              SET is_read = 1 
              WHERE notification_id = ? 
              AND user_id = ? 
              AND user_type = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('iis', $notificationId, $userID, $userRole);
    if (!$stmt->execute()) {
        throw new Exception("Query error: " . $stmt->error);
    }
    
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read',
        'affected' => $affected,
        'debug' => [
            'notification_id' => $notificationId,
            'user_id' => $userID,
            'role' => $userRole
        ]
    ]);
    
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ]);
}
?>