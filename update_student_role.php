<?php
session_start();
require 'configs/dbconnection.php';

// Check admin authentication
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Get and validate input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    exit(json_encode(['success' => false, 'message' => 'Invalid JSON data']));
}

$student_id = $input['student_id'] ?? null;
$action = $input['action'] ?? null;

// Additional validation
if (!$student_id || !is_numeric($student_id)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid student ID']));
}

if (!in_array($action, ['promote', 'demote'])) {
    exit(json_encode(['success' => false, 'message' => 'Invalid action']));
}

try {
    // First verify the student exists
    $check_stmt = $conn->prepare("SELECT studentID FROM students WHERE studentID = ?");
    $check_stmt->bind_param('i', $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        exit(json_encode(['success' => false, 'message' => 'Student not found']));
    }

    // Determine new role (0=admin, 1=student)
    $new_role = ($action === 'promote') ? 0 : 1;
    
    // Update the student's role
    $stmt = $conn->prepare("UPDATE students SET type = ? WHERE studentID = ?");
    $stmt->bind_param('ii', $new_role, $student_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Role updated successfully']);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database update failed',
            'error' => $conn->error
        ]);
    }
} catch (Exception $e) {
    error_log("Role update error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>