<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "ems";

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $database);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    error_log("Database connection error: " . $e->getMessage());

    // Return JSON response
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

