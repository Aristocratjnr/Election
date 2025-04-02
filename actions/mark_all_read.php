<?php
session_start();
require 'configs/dbconnection.php';

if (!isset($_SESSION['login_id'])) {
    http_response_code(401);
    exit();
}

$studentID = (int)$_POST['studentID'];
$studentType = $_POST['studentType'];

try {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 
                          WHERE studentID = ? AND student_type = ? AND is_read = 0");
    $stmt->bind_param('is', $studentID, $studentType);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}