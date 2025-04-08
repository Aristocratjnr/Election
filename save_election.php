<?php
require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = $_POST['name'] ?? '';
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    $status = $_POST['status'] ?? '';
    $visibility = $_POST['visibility'] ?? 'Public';
    
    // Validate inputs
    if (empty($name) || empty($startDate) || empty($endDate) || empty($status)) {
        header('Location: election.php?error=missing_fields');
        exit;
    }
    
    try {
        if ($action === 'edit' && isset($_POST['electionID'])) {
            // Update existing election
            $stmt = $conn->prepare("
                UPDATE elections 
                SET name = ?, startDate = ?, endDate = ?, status = ?, visibility = ?
                WHERE electionID = ?
            ");
            $stmt->bind_param("sssssi", 
                $name, 
                $startDate, 
                $endDate, 
                $status, 
                $visibility,
                $_POST['electionID']
            );
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                header('Location: election.php?success=updated');
                exit;
            }
        } else {
            // Create new election
            $stmt = $conn->prepare("
                INSERT INTO elections (name, startDate, endDate, status, visibility)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssss", 
                $name, 
                $startDate, 
                $endDate, 
                $status, 
                $visibility
            );
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                header('Location: election.php?success=created');
                exit;
            }
        }
    } catch (Exception $e) {
        header('Location: election.php?error=db_error');
        exit;
    }
    
    header('Location: election.php?error=unknown');
    exit;
}

// If accessed directly without POST data
header('Location: election.php');
exit;
?>