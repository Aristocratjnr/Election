<?php
require_once '../configs/dbconnection.php';
require_once '../configs/session.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if categories data is provided
if (!isset($_POST['categories']) || !is_array($_POST['categories'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category order data']);
    exit();
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Update each category's position
    foreach ($_POST['categories'] as $category) {
        if (!isset($category['categoryID']) || !isset($category['position'])) {
            throw new Exception('Invalid category data format');
        }

        $stmt = $conn->prepare("UPDATE categories SET position = ?, updated_at = NOW(), updated_by = ? WHERE categoryID = ?");
        $stmt->bind_param("iii", $category['position'], $_SESSION['login_id'], $category['categoryID']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update category position");
        }
    }

    // Log the activity
    $activityStmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, timestamp) VALUES (?, 'category_reorder', 'Updated category positions', NOW())");
    $activityStmt->bind_param("i", $_SESSION['login_id']);
    $activityStmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Category order updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    error_log("Error in update_category_order.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update category order: ' . $e->getMessage()
    ]);
}