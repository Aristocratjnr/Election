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
    // Validate request data
    if (!isset($_POST['categoryID']) || empty($_POST['categoryID'])) {
        throw new Exception('Category ID is required');
    }
    
    $categoryID = $_POST['categoryID'];
    
    // Check if category exists
    $checkCategory = $conn->prepare("SELECT categoryID FROM categories WHERE categoryID = ?");
    $checkCategory->bind_param('i', $categoryID);
    $checkCategory->execute();
    $categoryResult = $checkCategory->get_result();
    
    if ($categoryResult->num_rows === 0) {
        throw new Exception('Category does not exist');
    }
    
    // Check if category is used in any positions (if positions have a reference to categories)
    // This is a placeholder - you would need to adjust this based on your actual database schema
    $checkUsage = $conn->prepare("SELECT positionID FROM positions WHERE categoryID = ? LIMIT 1");
    if ($checkUsage) {
        $checkUsage->bind_param('i', $categoryID);
        $checkUsage->execute();
        $usageResult = $checkUsage->get_result();
        
        if ($usageResult->num_rows > 0) {
            throw new Exception('Cannot delete this category as it is being used in one or more positions');
        }
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Delete category
    $deleteCategory = $conn->prepare("DELETE FROM categories WHERE categoryID = ?");
    $deleteCategory->bind_param('i', $categoryID);
    $deleteCategory->execute();
    
    if ($deleteCategory->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete category');
    }
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    try {
        $conn->rollback();
    } catch (Exception $rollbackError) {
        // Ignore rollback errors
    }
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 