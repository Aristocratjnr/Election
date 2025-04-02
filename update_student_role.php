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

// Get the raw input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debugging - log received data
error_log("Received raw input: " . $input);
error_log("Decoded data: " . print_r($data, true));

// Validate JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON format',
        'error' => json_last_error_msg(),
        'received_data' => $input
    ]);
    exit();
}

// Validate required parameters
if (!isset($data['student_id']) || !isset($data['action'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters',
        'required' => ['student_id', 'action'],
        'received' => array_keys($data ?: [])
    ]);
    exit();
}

$studentID = filter_var($data['student_id'], FILTER_VALIDATE_INT);
$action = $data['action'];

// Validate student ID
if ($studentID === false || $studentID <= 0) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid student ID',
        'student_id_received' => $data['student_id'],
        'student_id_processed' => $studentID
    ]);
    exit();
}

// Validate action
if (!in_array($action, ['promote', 'demote'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action',
        'valid_actions' => ['promote', 'demote'],
        'action_received' => $action
    ]);
    exit();
}

try {
    // Determine new type (0=admin, 1=student)
    $newType = ($action === 'promote') ? 0 : 1;
    
    // Check if student exists first
    $checkStmt = $conn->prepare("SELECT studentID FROM students WHERE studentID = ?");
    $checkStmt->bind_param("i", $studentID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception("Student with ID $studentID not found");
    }

    // Update the student's type
    $updateStmt = $conn->prepare("UPDATE students SET type = ? WHERE studentID = ?");
    $updateStmt->bind_param("ii", $newType, $studentID);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Database update failed: " . $updateStmt->error);
    }

    // Verify the update
    if ($updateStmt->affected_rows === 0) {
        throw new Exception("No changes made - student type may already be set to this value");
    }

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Student role updated successfully',
        'student_id' => $studentID,
        'new_role' => ($newType === 0) ? 'admin' : 'student',
        'action_performed' => $action
    ]);

} catch (Exception $e) {
    error_log("Role update error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Error updating role: ' . $e->getMessage(),
        'student_id' => $studentID,
        'action' => $action
    ]);
}
?>