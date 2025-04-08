<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

if (isset($_GET['id'])) {
    $electionID = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM elections WHERE electionID = ?");
        $stmt->bind_param("i", $electionID);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            header('Location: election.php?success=deleted');
            exit;
        } else {
            header('Location: election.php?error=not_found');
            exit;
        }
        
    } catch (Exception $e) {
        header('Location: election.php?error=delete_failed');
        exit;
    }
} else {
    header('Location: election.php?error=invalid_request');
    exit;
}