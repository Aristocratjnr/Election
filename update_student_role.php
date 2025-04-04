<?php
// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'configs/dbconnection.php';

// Check admin authentication
if (!isset($_SESSION['login_id'])) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Not logged in']));
}

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Admin access required']));
}

// Get and validate input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid JSON data']));
}

$student_id = $input['student_id'] ?? null;
$action = $input['action'] ?? null;

// Additional validation
if (!$student_id || !is_numeric($student_id)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid student ID']));
}

if (!in_array($action, ['promote', 'demote'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid action']));
}

try {
    // First verify the student exists
    $check_stmt = $conn->prepare("SELECT studentID, role FROM students WHERE studentID = ?");
    $check_stmt->bind_param('i', $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        http_response_code(404);
        exit(json_encode(['success' => false, 'message' => 'Student not found']));
    }

    // Determine new role
    $new_role = ($action === 'promote') ? 'admin' : 'student';
    
    // Update the student's role (using the correct column name 'role')
    $stmt = $conn->prepare("UPDATE students SET role = ? WHERE studentID = ?");
    $stmt->bind_param('si', $new_role, $student_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Role updated successfully',
            'new_role' => $new_role
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Database update failed',
            'error' => $conn->error,
            'query_error' => $stmt->error
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Role update error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>