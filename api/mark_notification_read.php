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
require_once ROOT_PATH . '/configs/dbconnection.php';
require_once ROOT_PATH . '/configs/session.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Check if notification_id is provided
    if (!isset($data['notification_id']) || !is_numeric($data['notification_id'])) {
        throw new Exception('Invalid notification ID');
    }
    
    $notificationId = (int)$data['notification_id'];
    
    // Get user ID from session or request
    $userID = isset($_SESSION['login_id']) ? (int)$_SESSION['login_id'] : 0;
    
    // Validate user is logged in
    if ($userID <= 0) {
        throw new Exception('User not authenticated');
    }
    
    // Verify notification belongs to user before marking as read
    $checkQuery = "SELECT notification_id FROM notifications WHERE notification_id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('ii', $notificationId, $userID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Notification not found or not authorized');
    }
    
    // Update notification as read
    $updateQuery = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('i', $notificationId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to mark notification as read');
    }
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);
    
} catch (Exception $e) {
    http_response_code(200); // Always return 200 for API consistency
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>