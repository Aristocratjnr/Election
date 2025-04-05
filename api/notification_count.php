<?php
header('Content-Type: application/json');
require_once __DIR__ . '../configs/dbconnection.php';
require_once __DIR__ . '../configs/session.php';

try {
    // Get request parameters
    $userID = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'student';
    $lastCheck = isset($_GET['last_check']) ? $_GET['last_check'] : null;

    // Validate input
    if ($userID <= 0) {
        throw new Exception('Invalid user ID');
    }

    // Base query
    $query = "SELECT COUNT(*) AS count FROM notifications 
              WHERE user_id = ? AND user_type = ? AND is_read = 0";
    
    $params = [$userID, $userType];
    $types = 'is';

    // Add time filter if provided
    if ($lastCheck) {
        $query .= " AND created_at > ?";
        $params[] = $lastCheck;
        $types .= 's';
    }

    // Get unread count
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    // Get latest notification if new ones exist
    $latestNotification = null;
    if ($count > 0) {
        $latestQuery = "SELECT * FROM notifications 
                       WHERE user_id = ? AND user_type = ?
                       ORDER BY created_at DESC LIMIT 1";
        $latestStmt = $conn->prepare($latestQuery);
        $latestStmt->bind_param('is', $userID, $userType);
        $latestStmt->execute();
        $latestResult = $latestStmt->get_result();
        $latestNotification = $latestResult->fetch_assoc();
    }

    echo json_encode([
        'success' => true,
        'count' => $count,
        'latest_notification' => $latestNotification
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>