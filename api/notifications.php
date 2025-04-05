<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../configs/dbconnection.php';
require_once __DIR__ . '/../configs/session.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get request parameters
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $userID = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'student';

    // Validate input
    if ($userID <= 0) {
        throw new Exception('Invalid user ID');
    }

    // Prepare and execute query
    $limit = 10; // Number of notifications to load per request
    $query = "SELECT n.*, e.name AS election_name, e.status AS election_status,
                     c.position AS candidate_position, s.name AS candidate_name
              FROM notifications n
              LEFT JOIN elections e ON n.related_election = e.electionID
              LEFT JOIN candidates c ON n.related_candidate = c.candidateID
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
        } else {
            $row['time_ago'] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
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
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'has_more' => ($offset + $limit) < $total,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $conn->error ?? null
    ]);
}
?>