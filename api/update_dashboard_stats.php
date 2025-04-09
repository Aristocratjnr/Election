<?php
require_once '../configs/dbconnection.php';
require_once '../includes/auth_check.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    $updateType = isset($_POST['update_type']) ? $_POST['update_type'] : 'all';
    $updateData = [];
    
    // Update categories stats
    if ($updateType === 'categories' || $updateType === 'all') {
        $categoriesQuery = $conn->prepare("SELECT COUNT(*) as total FROM categories");
        $categoriesQuery->execute();
        $categoriesCount = $categoriesQuery->get_result()->fetch_assoc()['total'];
        
        $updateData['categories'] = $categoriesCount;
        
        // Update the session variable to reflect in the dashboard
        $_SESSION['dashboard_stats']['total_active_categories'] = $categoriesCount;
    }
    
    // For future expansion - update other dashboard stats based on update_type
    
    echo json_encode([
        'success' => true,
        'message' => 'Dashboard stats updated successfully',
        'data' => $updateData
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update dashboard stats: ' . $e->getMessage()
    ]);
} 