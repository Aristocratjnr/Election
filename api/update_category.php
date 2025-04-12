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
    
    // Get a valid student ID (first student in the database)
    $studentQuery = $conn->query("SELECT studentID FROM students LIMIT 1");
    if ($studentQuery->num_rows === 0) {
        throw new Exception('No student record found. Cannot update category.');
    }
    $studentID = $studentQuery->fetch_assoc()['studentID'];
    
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
    
    // Update category - without the description field
    $updateCategory = $conn->prepare(
        "UPDATE categories SET 
            electionID = ?, 
            name = ?, 
            updatedBy = ? 
        WHERE categoryID = ?"
    );
    $updateCategory->bind_param('isii', $electionID, $categoryName, $studentID, $categoryID);
    $updateCategory->execute();
    
    if ($updateCategory->affected_rows >= 0) { // Using >= 0 because affected_rows might be 0 if no changes were made
        // Get the updated category
        $updatedCategoryQuery = $conn->prepare(
            "SELECT c.*, e.name as election_name, s1.name as added_by_name, s2.name as updated_by_name 
             FROM categories c
             LEFT JOIN elections e ON c.electionID = e.electionID
             LEFT JOIN students s1 ON c.addedBy = s1.studentID
             LEFT JOIN students s2 ON c.updatedBy = s2.studentID
             WHERE c.categoryID = ?"
        );
        $updatedCategoryQuery->bind_param('i', $categoryID);
        $updatedCategoryQuery->execute();
        $updatedCategory = $updatedCategoryQuery->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully',
            'category' => $updatedCategory
        ]);
    } else {
        throw new Exception('Failed to update category');
    }
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 