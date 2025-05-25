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