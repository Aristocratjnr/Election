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

// Additional validation
if (!$student_id || !is_numeric($student_id)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid student ID']));
}

try {
    // First verify the student exists and get their email
    $check_stmt = $conn->prepare("SELECT studentID, email FROM students WHERE studentID = ?");
    $check_stmt->bind_param('i', $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        exit(json_encode(['success' => false, 'message' => 'Student not found']));
    }
    
    $student = $check_result->fetch_assoc();

    // Generate a temporary password
    $temp_password = bin2hex(random_bytes(4)); // 8-character temporary password
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    
    // Update the student's password
    $stmt = $conn->prepare("UPDATE students SET password = ? WHERE studentID = ?");
    $stmt->bind_param('si', $hashed_password, $student_id);
    
    if ($stmt->execute()) {
        // For development only - show the temp password
        // In production, you would email this to the student
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successful',
            'temp_password' => $temp_password,
            'student_id' => $student_id // Echo back for debugging
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database update failed',
            'error' => $conn->error
        ]);
    }
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>