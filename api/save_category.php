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
    if (!isset($_POST['electionID']) || !isset($_POST['name']) || 
        empty($_POST['electionID']) || empty($_POST['name'])) {
        throw new Exception('Election ID and category name are required');
    }
    
    $electionID = $_POST['electionID'];
    $categoryName = $_POST['name'];
    $description = $_POST['description'] ?? null;
    $adminID = $_SESSION['login_id'];
    
    // Check if election exists
    $checkElection = $conn->prepare("SELECT electionID FROM elections WHERE electionID = ?");
    $checkElection->bind_param('i', $electionID);
    $checkElection->execute();
    $electionResult = $checkElection->get_result();
    
    if ($electionResult->num_rows === 0) {
        throw new Exception('Selected election does not exist');
    }
    
    // Check if category with same name exists in this election
    $checkDuplicate = $conn->prepare("SELECT categoryID FROM categories WHERE electionID = ? AND name = ?");
    $checkDuplicate->bind_param('is', $electionID, $categoryName);
    $checkDuplicate->execute();
    $duplicateResult = $checkDuplicate->get_result();
    
    if ($duplicateResult->num_rows > 0) {
        throw new Exception('A category with this name already exists for the selected election');
    }
    
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO categories (electionID, name, description, addedBy, createdAt) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('issi', $electionID, $categoryName, $description, $adminID);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Category added successfully'
        ]);
    } else {
        throw new Exception('Failed to add category');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}