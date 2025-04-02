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

// Validate input
if ($studentID <= 0) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit();
}

try {
    // Generate a random temporary password
    $tempPassword = bin2hex(random_bytes(4)); // 8-character temporary password
    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Update the student's password in the database
    $stmt = $conn->prepare("UPDATE students SET password = ? WHERE studentID = ?");
    $stmt->bind_param("si", $hashedPassword, $studentID);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update password: " . $stmt->error);
    }

    // Return success with the temporary password (in a real app, you might email this instead)
    echo json_encode([
        'success' => true,
        'message' => 'Password reset successfully',
        'temp_password' => $tempPassword // Only for demo purposes - remove in production
    ]);

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Error resetting password: ' . $e->getMessage()
    ]);
}
?>