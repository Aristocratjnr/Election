<?php
session_start();
include 'configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $studentID = $_POST['student'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $department = $_POST['deparment'] ?? '';
    $phone = $_POST['contact'] ?? '';

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL
    $stmt = $conn->prepare("INSERT INTO students (studentID, email, password, department, contactNumber) VALUES (?, ?, ?, ?, ?)");

    if ($stmt) {
        // 5 parameters, so use 'sssss'
        $stmt->bind_param("sssss", $studentID, $email, $hashedPassword, $department, $phone);

        if ($stmt->execute()) {
            echo "Registration successful.";
           
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Statement failed to prepare.";
    }

    $conn->close();
}
?>
