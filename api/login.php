<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../configs/dbconnection.php';
require_once __DIR__ . '/../configs/session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check for required fields
        if (!isset($_POST['studentID']) || !isset($_POST['password'])) {
            throw new Exception('Student ID and password are required');
        }

        $studentID = filter_var($_POST['studentID'], FILTER_SANITIZE_NUMBER_INT);
        $password = $_POST['password'];
        
        // Validate student ID
        if (!$studentID) {
            throw new Exception('Invalid student ID');
        }
        
        // Query student from database
        $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("i", $studentID);
        if (!$stmt->execute()) {
            throw new Exception("Query error: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Student not found');
        }
        
        $student = $result->fetch_assoc();
        $stmt->close();
        
        // Verify password
        if (!password_verify($password, $student['password'])) {
            throw new Exception('Invalid password');
        }
        
        // Check if account is active
        if ($student['status'] !== 'Active') {
            throw new Exception('Account is inactive. Please contact an administrator.');
        }
        
        // Set session variables
        $_SESSION['login_id'] = $student['studentID'];
        $_SESSION['name'] = $student['name'];
        $_SESSION['role'] = $student['role'];
        
        // Update last login time
        $updateStmt = $conn->prepare("UPDATE students SET last_login = NOW() WHERE studentID = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("i", $student['studentID']);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'role' => $student['role'],
            'redirectUrl' => $student['role'] === 'admin' ? 'dashboard.php' : 'student.php'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // For GET requests, just return basic information
    echo json_encode([
        'success' => true,
        'message' => 'Login API endpoint',
        'status' => 'available'
    ]);
}
?>