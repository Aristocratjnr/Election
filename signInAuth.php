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

    $stmt = $conn->prepare("SELECT studentID, name, password FROM students WHERE studentID = ?");
    if (!$stmt) {
        throw new Exception('Database error');
    }

    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid student ID or password');
    }

    $student = $result->fetch_assoc();

    if (!password_verify($password, $student['password'])) {
        throw new Exception('Invalid student ID or password');
    }

    session_regenerate_id(true);
    $_SESSION = [
        'login_id' => $student['studentID'],
        'student_name' => $student['name'],
        'last_activity' => time()
    ];

    $response = [
        'status' => 'success',
        'redirect_url' => 'dashboard.php'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Login Error: ' . $e->getMessage());
}

$stmt->close();
$conn->close();
echo json_encode($response);
exit;