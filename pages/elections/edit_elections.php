<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $electionID = $_POST['electionID'] ?? null;
    
    // Sanitize inputs
    $name = trim($_POST['name']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];
    $visibility = $_POST['visibility'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Election name is required';
    }
    
    if (empty($startDate) || empty($endDate)) {
        $errors[] = 'Both start and end dates are required';
    } else {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        if ($end < $start) {
            $errors[] = 'End date cannot be earlier than start date';
        }
    }
    
    if (empty($status)) {
        $errors[] = 'Status is required';
    }
    
    // If no errors, proceed with database operation
    if (empty($errors)) {
        try {
            if ($action === 'create') {
                // Check if election name already exists
                $stmt = $conn->prepare("SELECT electionID FROM elections WHERE name = ?");
                $stmt->bind_param('s', $name);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    header('Location: elections.php?error=name_exists');
                    exit();
                }
                
                // Create new election
                $stmt = $conn->prepare("INSERT INTO elections (name, startDate, endDate, status, visibility) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssss', $name, $startDate, $endDate, $status, $visibility);
                $stmt->execute();
                
                header('Location: elections.php?success=created');
                exit();
                
            } elseif ($action === 'edit' && $electionID) {
                // Check if election name already exists (excluding current election)
                $stmt = $conn->prepare("SELECT electionID FROM elections WHERE name = ? AND electionID != ?");
                $stmt->bind_param('si', $name, $electionID);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    header("Location: elections.php?action=edit&id=$electionID&error=name_exists");
                    exit();
                }
                
                // Update election
                $stmt = $conn->prepare("UPDATE elections SET name = ?, startDate = ?, endDate = ?, status = ?, visibility = ? WHERE electionID = ?");
                $stmt->bind_param('sssssi', $name, $startDate, $endDate, $status, $visibility, $electionID);
                $stmt->execute();
                
                header("Location: elections.php?success=updated");
                exit();
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            header('Location: elections.php?error=db_error');
            exit();
        }
    } else {
        // Redirect back with errors
        $error = urlencode(implode(', ', $errors));
        if ($action === 'edit' && $electionID) {
            header("Location: elections.php?action=edit&id=$electionID&error=$error");
        } else {
            header("Location: elections.php?action=create&error=$error");
        }
        exit();
    }
} else {
    header('Location: elections.php');
    exit();
}