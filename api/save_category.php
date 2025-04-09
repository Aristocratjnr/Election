<?php
$base_path = dirname(__FILE__, 2); // Go up one level from api folder
require_once $base_path . '/configs/dbconnection.php';
require_once $base_path . '/includes/auth_check.php';

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
    if (!isset($_POST['electionID']) || !isset($_POST['name']) || empty($_POST['electionID']) || empty($_POST['name'])) {
        throw new Exception('Election ID and category name are required');
    }
    
    $electionID = $_POST['electionID'];
    $categoryName = $_POST['name'];
    
    // Get a valid student ID (first student in the database)
    $studentQuery = $conn->query("SELECT studentID FROM students LIMIT 1");
    if ($studentQuery->num_rows === 0) {
        throw new Exception('No student record found. Cannot create category.');
    }
    $studentID = $studentQuery->fetch_assoc()['studentID'];
    
    // Check if election exists
    $checkElection = $conn->prepare("SELECT electionID FROM elections WHERE electionID = ?");
    $checkElection->bind_param('i', $electionID);
    $checkElection->execute();
    $electionResult = $checkElection->get_result();
    
    if ($electionResult->num_rows === 0) {
        throw new Exception('Selected election does not exist');
    }
    
    // Check if category name already exists for this election
    $checkCategory = $conn->prepare("SELECT categoryID FROM categories WHERE electionID = ? AND name = ?");
    $checkCategory->bind_param('is', $electionID, $categoryName);
    $checkCategory->execute();
    $categoryResult = $checkCategory->get_result();
    
    if ($categoryResult->num_rows > 0) {
        throw new Exception('A category with this name already exists for the selected election');
    }
    
    // Log the incoming data for debugging
    error_log("Adding category: Election ID: {$electionID}, Name: {$categoryName}, Student ID: {$studentID}");
    
    // Insert new category - without the description field
    $insertCategory = $conn->prepare("INSERT INTO categories (electionID, name, addedBy) VALUES (?, ?, ?)");
    
    if (!$insertCategory) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $bindResult = $insertCategory->bind_param('isi', $electionID, $categoryName, $studentID);
    if (!$bindResult) {
        throw new Exception("Binding parameters failed: " . $insertCategory->error);
    }
    
    $executeResult = $insertCategory->execute();
    if (!$executeResult) {
        throw new Exception("Execute failed: " . $insertCategory->error);
    }
    
    if ($insertCategory->affected_rows > 0) {
        // Get the newly created category
        $newCategoryQuery = $conn->prepare(
            "SELECT c.*, e.name as election_name, s.name as added_by_name 
             FROM categories c
             LEFT JOIN elections e ON c.electionID = e.electionID
             LEFT JOIN students s ON c.addedBy = s.studentID
             WHERE c.categoryID = ?"
        );
        $newCategoryID = $conn->insert_id;
        $newCategoryQuery->bind_param('i', $newCategoryID);
        $newCategoryQuery->execute();
        $newCategory = $newCategoryQuery->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'categoryID' => $newCategoryID,
            'category' => $newCategory
        ]);
    } else {
        throw new Exception('Failed to create category');
    }
    
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 