<?php
session_start();
header('Content-Type: application/json');

include 'configs/dbconnection.php';

$studentID = $_POST['studentID'] ?? '';
$password = $_POST['password'] ?? '';

if ($studentID && $password) {
    $stmt = $conn->prepare("SELECT studentID, name, password FROM students WHERE studentID = ?");
    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();

        if (password_verify($password, $student['password'])) {
            $_SESSION['login_id'] = $student['studentID'];
            $_SESSION['student_name'] = $student['name'];

            echo json_encode([
                'success' => true,
                'name' => $student['name']
            ]);
            exit;
        }
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid Student ID or Password'
]);
