<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../configs/dbconnection.php';

// Simple session handling without forcing options
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get request parameters
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $userID = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_SESSION['login_id']) ? (int)$_SESSION['login_id'] : 0);
    $userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'student';

    // Validate input
    if ($userID <= 0) {
        // Return empty results instead of throwing error
        echo json_encode([
            'success' => true,
            'notifications' => [],
            'has_more' => false,
            'total' => 0
        ]);
        exit;
    }

    // Prepare and execute query
    $limit = 10; // Number of notifications to load per request
    $query = "SELECT n.*, e.name AS election_name, e.status AS election_status,
                     p.title AS position_title, s.name AS candidate_name
              FROM notifications n
              LEFT JOIN elections e ON n.related_election = e.electionID
              LEFT JOIN candidates c ON n.related_candidate = c.candidateID
              LEFT JOIN positions p ON c.positionID = p.positionID
              LEFT JOIN students s ON c.studentID = s.studentID
              WHERE n.user_id = ? AND n.user_type = ?
              ORDER BY n.created_at DESC 
              LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('isii', $userID, $userType, $limit, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        // Format notification
        switch ($row['type']) {
            case 'election':
                $row['icon'] = 'bi-megaphone';
                $row['bg_class'] = 'bg-primary-light';
                $row['badge_class'] = 'bg-primary';
                break;
            case 'vote':
                $row['icon'] = 'bi-check-circle';
                $row['bg_class'] = 'bg-success-light';
                $row['badge_class'] = 'bg-success';
                break;
            case 'result':
                $row['icon'] = 'bi-graph-up';
                $row['bg_class'] = 'bg-info-light';
                $row['badge_class'] = 'bg-info';
                break;
            case 'candidate':
                $row['icon'] = 'bi-person-badge';
                $row['bg_class'] = 'bg-warning-light';
                $row['badge_class'] = 'bg-warning';
                break;
            case 'system':
                $row['icon'] = 'bi-gear';
                $row['bg_class'] = 'bg-secondary-light';
                $row['badge_class'] = 'bg-secondary';
                break;
            default:
                $row['icon'] = 'bi-bell';
                $row['bg_class'] = 'bg-secondary-light';
                $row['badge_class'] = 'bg-secondary';
        }
        
        // Format time
        $createdAt = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $now->diff($createdAt);
        
        if ($interval->d > 0) {
            $row['time_ago'] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $row['time_ago'] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $row['time_ago'] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $row['time_ago'] = 'Just now';
        }
        
        $notifications[] = $row;
    }
    
    // Check if more notifications exist
    $totalQuery = "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND user_type = ?";
    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->bind_param('is', $userID, $userType);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];
    
    // Count unread notifications
    $unreadQuery = "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = ? AND user_type = ? AND is_read = 0";
    $unreadStmt = $conn->prepare($unreadQuery);
    $unreadStmt->bind_param('is', $userID, $userType);
    $unreadStmt->execute();
    $unreadResult = $unreadStmt->get_result();
    $unread = $unreadResult->fetch_assoc()['unread'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'has_more' => ($offset + $limit) < $total,
        'total' => $total,
        'unread' => $unread
    ]);
    
} catch (Exception $e) {
    // Always return 200 with error details in the body
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $conn->error ?? null,
        'notifications' => []
    ]);
}
?>