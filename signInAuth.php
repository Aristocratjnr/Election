<?php
session_start();
header('Content-Type: application/json');
include 'configs/dbconnection.php';

$response = ['status' => 'error', 'message' => 'Login failed'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $studentID = trim($_POST['student'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($studentID)) {
        throw new Exception('Student ID is required');
    }

    if (empty($password)) {
        throw new Exception('Password is required');
    }

    // Query to fetch user details including role
    $stmt = $conn->prepare("SELECT studentID, name, password, role FROM students WHERE LOWER(studentID) = LOWER(?)");
    if (!$stmt) {
        throw new Exception('Database error');
    }

    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log('No user found with studentID: ' . $studentID);  // Debugging
        throw new Exception('Invalid student ID or password');
    }

    $user = $result->fetch_assoc();

    // Debugging: Log user data fetched from DB
    error_log('Fetched user: ' . print_r($user, true));

    // Check if the password is correct
    if (!password_verify($password, $user['password'])) {
        error_log('Password doesn’t match for studentID: ' . $studentID);  // Debugging
        throw new Exception('Invalid student ID or password');
    }

    session_regenerate_id(true);
    
    // Store session variables
    $_SESSION = [
        'login_id' => $user['studentID'],
        'student_name' => $user['name'],
        'role' => $user['role'],
        'last_activity' => time()
    ];

    // Debugging: Log session data
    error_log('Session data: ' . print_r($_SESSION, true));

    // Check the user role and redirect accordingly
    if (strtolower(trim($user['role'])) === 'admin') {
        $response = [
            'status' => 'success',
            'redirect_url' => 'dashboard.php'  // Admin dashboard URL
        ];
    } else {
        $response = [
            'status' => 'success',
            'redirect_url' => 'student.php'  // Student dashboard URL
        ];
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Login Error: ' . $e->getMessage());
}

$stmt->close();
$conn->close();
echo json_encode($response);
exit;
?>
