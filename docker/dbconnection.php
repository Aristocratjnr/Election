<?php

$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'ems';

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Define log directory and file
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/error.log';

try {
    // Ensure the logs directory exists
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $database);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Log error to file
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[$timestamp] DB Connection Error: " . $e->getMessage() . "\n";
    error_log($errorMessage, 3, $logFile);

    // Return generic JSON error message to frontend
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "Internal server error. Please try again later."
    ]);
    exit;
}