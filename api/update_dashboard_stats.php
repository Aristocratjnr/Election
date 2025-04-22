<?php
require_once '../configs/dbconnection.php';
require_once '../configs/session.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Update category count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM categories");
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'];
    
    $_SESSION['dashboard_stats']['total_active_categories'] = $count;
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_active_categories' => $count
        ]
    ]);
} catch (Exception $e) {
    error_log("Error in update_dashboard_stats.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update dashboard stats']);
}