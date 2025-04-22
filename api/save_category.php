<?php
require_once '../configs/dbconnection.php';
require_once '../configs/session.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if required data is provided
if (!isset($_POST['electionID']) || !isset($_POST['name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$electionID = $_POST['electionID'];
$name = trim($_POST['name']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate inputs
if (empty($name) || empty($electionID)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate name length
if (strlen($name) < 3 || strlen($name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Category name must be between 3 and 100 characters']);
    exit();
}

try {
    // Check if election exists and is active
    $electionStmt = $conn->prepare("SELECT status FROM elections WHERE electionID = ?");
    $electionStmt->bind_param("i", $electionID);
    $electionStmt->execute();
    $electionResult = $electionStmt->get_result();
    
    if ($electionResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid election selected']);
        exit();
    }
    
    $electionStatus = $electionResult->fetch_assoc()['status'];
    if ($electionStatus === 'Completed') {
        echo json_encode(['success' => false, 'message' => 'Cannot add categories to completed elections']);
        exit();
    }

    // Check if category name already exists for this election
    $checkStmt = $conn->prepare("SELECT categoryID FROM categories WHERE name = ? AND electionID = ?");
    $checkStmt->bind_param("si", $name, $electionID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A category with this name already exists in this election']);
        exit();
    }
    
    // Insert new category with description and creator info
    $stmt = $conn->prepare("INSERT INTO categories (name, description, electionID, created_at, created_by) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssis", $name, $description, $electionID, $_SESSION['login_id']);
    
    if ($stmt->execute()) {
        $categoryID = $stmt->insert_id;
        
        // Log the activity
        $activityStmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, related_id, description, timestamp) VALUES (?, 'category_created', ?, ?, NOW())");
        $activityDesc = "Created new category: " . $name;
        $activityStmt->bind_param("iis", $_SESSION['login_id'], $categoryID, $activityDesc);
        $activityStmt->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Category added successfully',
            'categoryID' => $categoryID,
            'category' => [
                'id' => $categoryID,
                'name' => $name,
                'description' => $description,
                'electionID' => $electionID
            ]
        ]);
    } else {
        throw new Exception("Failed to add category");
    }
} catch (Exception $e) {
    error_log("Error in save_category.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to add category: ' . $e->getMessage()]);
}