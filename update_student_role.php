<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check authentication
session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
require 'configs/dbconnection.php';

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);
$studentID = isset($data['student_id']) ? intval($data['student_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

// Validate input
if ($studentID <= 0 || !in_array($action, ['promote', 'demote'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit();
}

try {
    // Determine new status based on action
    $newStatus = ($action === 'promote') ? 'Inactive' : 'Active'; // Assuming 'Inactive' means admin
    
    // Update the student's status in the database
    $stmt = $conn->prepare("UPDATE students SET status = ? WHERE studentID = ?");
    $stmt->bind_param("si", $newStatus, $studentID);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update role: " . $stmt->error);
    }

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Role updated successfully',
        'new_role' => ($action === 'promote') ? 'admin' : 'student'
    ]);

} catch (Exception $e) {
    error_log("Role update error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Error updating role: ' . $e->getMessage()
    ]);
}
?>