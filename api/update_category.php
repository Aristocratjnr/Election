<?php
// Use dirname to resolve paths correctly regardless of where the script is included from
$base_path = dirname(__FILE__, 2); // Go up one level from api folder
require_once $base_path . '../configs/dbconnection.php';
require_once $base_path . '../includes/auth_check.php';

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
    if (!isset($_POST['categoryID']) || !isset($_POST['electionID']) || !isset($_POST['name']) || 
        empty($_POST['categoryID']) || empty($_POST['electionID']) || empty($_POST['name'])) {
        throw new Exception('Category ID, Election ID and category name are required');
    }
    
    $categoryID = $_POST['categoryID'];
    $electionID = $_POST['electionID'];
    $categoryName = $_POST['name'];
    $description = $_POST['description'] ?? null;
    $adminID = $_SESSION['login_id'];
    
    // Check if category exists
    $checkCategory = $conn->prepare("SELECT categoryID FROM categories WHERE categoryID = ?");
    $checkCategory->bind_param('i', $categoryID);
    $checkCategory->execute();
    $categoryResult = $checkCategory->get_result();
    
    if ($categoryResult->num_rows === 0) {
        throw new Exception('Category does not exist');
    }
    
    // Check if election exists
    $checkElection = $conn->prepare("SELECT electionID FROM elections WHERE electionID = ?");
    $checkElection->bind_param('i', $electionID);
    $checkElection->execute();
    $electionResult = $checkElection->get_result();
    
    if ($electionResult->num_rows === 0) {
        throw new Exception('Selected election does not exist');
    }
    
    // Check if a different category with the same name exists for this election
    $checkDuplicate = $conn->prepare("SELECT categoryID FROM categories WHERE electionID = ? AND name = ? AND categoryID != ?");
    $checkDuplicate->bind_param('isi', $electionID, $categoryName, $categoryID);
    $checkDuplicate->execute();
    $duplicateResult = $checkDuplicate->get_result();
    
    if ($duplicateResult->num_rows > 0) {
        throw new Exception('A category with this name already exists for the selected election');
    }
    
    // Update category
    $stmt = $conn->prepare(
        "UPDATE categories SET 
            electionID = ?, 
            name = ?, 
            description = ?,
            updatedBy = ?,
            updatedAt = NOW()
        WHERE categoryID = ?"
    );
    $stmt->bind_param('issii', $electionID, $categoryName, $description, $adminID, $categoryID);
    $stmt->execute();
    
    if ($stmt->affected_rows >= 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update category');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}