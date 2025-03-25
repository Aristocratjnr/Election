<?php
include("configs/dbconnection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Print received POST data
    print_r($_POST);

    // Ensure required fields are not empty
    if (empty($_POST["student_id"]) || empty($_POST["email"]) || empty($_POST["password"])) {
        die("Missing required fields.");
    }

    // Sanitize and process inputs
    $student_id = trim($_POST["student_id"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Secure password hashing

    // Ensure column names match your database table
    $stmt = $conn->prepare("INSERT INTO Students (studentNumber, email, password, registrationDate) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $student_id, $email, $password);

    if ($stmt->execute()) {
        // Success message and redirect
        echo "<script>
                alert('Registration successful! Redirecting to login...');
                window.location.href='login.php';
              </script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
