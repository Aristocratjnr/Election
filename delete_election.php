<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify the request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: elections.php?error=invalid_method');
    exit;
}

// Validate election ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: elections.php?error=invalid_id');
    exit;
}

$electionID = (int)$_GET['id'];

try {
    // First verify the election exists
    $checkStmt = $conn->prepare("SELECT electionID FROM elections WHERE electionID = ?");
    $checkStmt->bind_param("i", $electionID);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows === 0) {
        header('Location: elections.php?error=not_found');
        exit;
    }
    $checkStmt->close();

  
    $deleteStmt = $conn->prepare("DELETE FROM elections WHERE electionID = ?");
    $deleteStmt->bind_param("i", $electionID);
    $deleteStmt->execute();
    
    if ($deleteStmt->affected_rows > 0) {
        header('Location: elections.php?success=deleted');
    } else {
        
        header('Location: elections.php?error=delete_failed');
    }
    
    $deleteStmt->close();
    
} catch (Exception $e) {
    error_log("Election deletion error: " . $e->getMessage());
    header('Location: elections.php?error=database_error');
} finally {
    $conn->close();
    exit;
}