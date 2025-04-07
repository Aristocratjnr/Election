<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Get form data
$action = $_POST['action'] ?? '';
$electionID = $_POST['electionID'] ?? '';
$name = $_POST['name'] ?? '';
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';
$status = $_POST['status'] ?? '';
$visibility = $_POST['visibility'] ?? 'Public';

// Validate required fields
if (empty($name) || empty($startDate) || empty($endDate) || empty($status)) {
    header('Location: elections.php?error=missing_fields');
    exit;
}

try {
    if ($action === 'edit' && !empty($electionID)) {
        // Update existing election
        $stmt = $conn->prepare("UPDATE elections SET name = ?, startDate = ?, endDate = ?, status = ?, visibility = ? WHERE electionID = ?");
        $stmt->bind_param('sssssi', $name, $startDate, $endDate, $status, $visibility, $electionID);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            header('Location: elections.php?success=updated');
        } else {
            header('Location: elections.php?error=update_failed');
        }
    } else {
        // Create new election
        $stmt = $conn->prepare("INSERT INTO elections (name, startDate, endDate, status, visibility) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $startDate, $endDate, $status, $visibility);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            header('Location: elections.php?success=created');
        } else {
            header('Location: elections.php?error=creation_failed');
        }
    }
} catch (Exception $e) {
    header('Location: elections.php?error=database_error');
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
exit; 