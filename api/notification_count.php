<?php
header('Content-Type: application/json');
require_once __DIR__ . '../configs/dbconnection.php';
require_once __DIR__ . '../configs/session.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

try {
    // Validate session
    if (!isset($_SESSION['login_id'])) {
        throw new Exception('Unauthorized access', 401);
    }

    // Use session ID for security
    $userID = (int)$_SESSION['login_id'];
    $userType = 'student'; // Hardcoded as your system appears student-focused
    
    // Prepare query with index-friendly conditions
    $query = "SELECT COUNT(*) AS count FROM notifications 
              WHERE user_id = ? AND user_type = ? AND is_read = 0";
    
    // Execute query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('is', $userID, $userType);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $count = (int)$result->fetch_assoc()['count'];

    // Return minimal response for better performance
    echo json_encode([
        'success' => true,
        'count' => $count,
        'timestamp' => time() // Useful for client-side tracking
    ]);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>