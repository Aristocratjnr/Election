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
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Check if category exists
    $checkCategory = $conn->prepare("SELECT categoryID FROM categories WHERE categoryID = ?");
    $checkCategory->bind_param('i', $categoryID);
    $checkCategory->execute();
    $categoryResult = $checkCategory->get_result();
    
    if ($categoryResult->num_rows === 0) {
        throw new Exception('Category does not exist');
    }
    
    // Delete associated candidates first (if any)
    $deleteCandidates = $conn->prepare("DELETE FROM candidates WHERE categoryID = ?");
    $deleteCandidates->bind_param('i', $categoryID);
    $deleteCandidates->execute();
    
    // Delete associated votes (if any)
    $deleteVotes = $conn->prepare("DELETE FROM votes WHERE categoryID = ?");
    $deleteVotes->bind_param('i', $categoryID);
    $deleteVotes->execute();
    
    // Finally delete the category
    $deleteCategory = $conn->prepare("DELETE FROM categories WHERE categoryID = ?");
    $deleteCategory->bind_param('i', $categoryID);
    $deleteCategory->execute();
    
    if ($deleteCategory->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Category and associated data deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete category');
    }
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if ($conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}