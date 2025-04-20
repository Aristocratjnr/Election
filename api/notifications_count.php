<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../configs/dbconnection.php';

// Simple session handling without forcing options
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always return a valid JSON response, even if not logged in
try {
    $userID = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_SESSION['login_id']) ? (int)$_SESSION['login_id'] : 0);
    $userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'student';
    
    // If not logged in, return zero count instead of error
    if ($userID <= 0) {
        echo json_encode([
            'success' => true,
            'count' => 0,
            'latest_notification' => null,
            'timestamp' => time()
        ]);
        exit;
    }
    
    // Prepare query with index-friendly conditions
    $query = "SELECT COUNT(*) AS count FROM notifications 
              WHERE user_id = ? AND user_type = ? AND is_read = 0";
    
    // Execute query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->bind_param('is', $userID, $userType);
    if (!$stmt->execute()) {
        throw new Exception("Query error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $count = (int)$result->fetch_assoc()['count'];
    $stmt->close();

    // Get latest unread notification if any exist
    $latestNotification = null;
    if ($count > 0) {
        $latestQuery = "SELECT notification_id, title, message, type, created_at 
                      FROM notifications 
                      WHERE user_id = ? AND user_type = ? AND is_read = 0 
                      ORDER BY created_at DESC LIMIT 1";
        $latestStmt = $conn->prepare($latestQuery);
        $latestStmt->bind_param('is', $userID, $userType);
        $latestStmt->execute();
        $latestResult = $latestStmt->get_result();
        if ($latestResult && $latestResult->num_rows > 0) {
            $latestNotification = $latestResult->fetch_assoc();
            $latestStmt->close();
        }
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'count' => $count,
        'latest_notification' => $latestNotification,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    // Always return 200 OK with error in JSON to prevent redirect issues
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'count' => 0
    ]);
}
?>