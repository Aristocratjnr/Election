<?php
header('Content-Type: application/json');
require_once '../configs/dbconnection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get parameters from request
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$last_check = isset($_GET['last_check']) ? $_GET['last_check'] : '';

// Validate parameters
if (!$user_id || !$user_type || !$last_check) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

try {
    // Convert last_check to MySQL datetime format
    $last_check_date = new DateTime($last_check);
    $formatted_last_check = $last_check_date->format('Y-m-d H:i:s');

    // Count unread notifications
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? 
        AND user_type = ? 
        AND is_read = 0 
        AND created_at > ?
    ");
    
    $stmt->bind_param('iss', $user_id, $user_type, $formatted_last_check);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'count' => (int)$data['count']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

?>