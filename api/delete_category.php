<?php
require_once '../configs/dbconnection.php';
require_once '../configs/session.php';

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if category ID is provided
if (!isset($_POST['categoryID'])) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit();
}

$categoryID = $_POST['categoryID'];

try {
    // Check if category exists
    $checkStmt = $conn->prepare("SELECT categoryID FROM categories WHERE categoryID = ?");
    $checkStmt->bind_param("i", $categoryID);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Category not found']);
        exit();
    }
    
    // Delete the category
    $stmt = $conn->prepare("DELETE FROM categories WHERE categoryID = ?");
    $stmt->bind_param("i", $categoryID);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Category deleted successfully'
        ]);
    } else {
        throw new Exception("Failed to delete category");
    }
} catch (Exception $e) {
    error_log("Error in delete_category.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to delete category: ' . $e->getMessage()]);
}