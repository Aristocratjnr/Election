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
if (!isset($_POST['categoryID']) || !isset($_POST['electionID']) || !isset($_POST['name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$categoryID = $_POST['categoryID'];
$electionID = $_POST['electionID'];
$name = trim($_POST['name']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate inputs
if (empty($name) || empty($electionID) || empty($categoryID)) {
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
        echo json_encode(['success' => false, 'message' => 'Cannot modify categories in completed elections']);
        exit();
    }

    // Check if category exists
    $checkCategoryStmt = $conn->prepare("SELECT electionID FROM categories WHERE categoryID = ?");
    $checkCategoryStmt->bind_param("i", $categoryID);
    $checkCategoryStmt->execute();
    
    if ($checkCategoryStmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Category not found']);
        exit();
    }

    // Check if category name already exists in this election (excluding current category)
    $checkStmt = $conn->prepare("SELECT categoryID FROM categories WHERE name = ? AND electionID = ? AND categoryID != ?");
    $checkStmt->bind_param("sii", $name, $electionID, $categoryID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A category with this name already exists in this election']);
        exit();
    }
    
    // Update category
    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, electionID = ?, updated_at = NOW(), updated_by = ? WHERE categoryID = ?");
    $stmt->bind_param("ssiii", $name, $description, $electionID, $_SESSION['login_id'], $categoryID);
    
    if ($stmt->execute()) {
        // Log the activity
        $activityStmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, related_id, description, timestamp) VALUES (?, 'category_updated', ?, ?, NOW())");
        $activityDesc = "Updated category: " . $name;
        $activityStmt->bind_param("iis", $_SESSION['login_id'], $categoryID, $activityDesc);
        $activityStmt->execute();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Category updated successfully',
            'category' => [
                'id' => $categoryID,
                'name' => $name,
                'description' => $description,
                'electionID' => $electionID
            ]
        ]);
    } else {
        throw new Exception("Failed to update category");
    }
} catch (Exception $e) {
    error_log("Error in update_category.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update category: ' . $e->getMessage()]);
}