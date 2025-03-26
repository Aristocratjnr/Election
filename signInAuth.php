<?php
session_start();
include 'configs/dbconnection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    // Check if student exists
    $stmt = $conn->prepare("SELECT studentID, password FROM Students WHERE studentID = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['student_id'] = $id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid student ID or password.";
        }
    } else {
        $error = "No account found with this student ID.";
    }

    $stmt->close();
    $conn->close();
}
