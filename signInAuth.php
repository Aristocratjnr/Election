<?php
session_start();
include 'configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentID = trim($_POST['student']);
    $password = trim($_POST['password']);

    if (empty($studentID) || empty($password)) {
        header("Location: login.php?error=empty");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['login_id'] = $student['studentID'];
        $_SESSION['student_name'] = $student['name'];

        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: login.php?error=invalid");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
