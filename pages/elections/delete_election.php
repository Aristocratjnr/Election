<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

if (isset($_GET['id'])) {
    $electionID = (int)$_GET['id'];
    
    try {
        
        // Then delete the election
        $stmt = $conn->prepare("DELETE FROM elections WHERE electionID = ?");
        $stmt->bind_param('i', $electionID);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            header('Location: elections.php?success=deleted');
        } else {
            header('Location: elections.php?error=not_found');
        }
        exit();
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        header('Location: elections.php?error=delete_failed');
        exit();
    }
} else {
    header('Location: elections.php');
    exit();
}