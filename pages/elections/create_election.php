<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $electionID = $_POST['electionID'] ?? null;
    $name = trim($_POST['name']);
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];

    // Validation
    if (empty($name) || empty($startDate) || empty($endDate)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: elections.php?action=' . ($electionID ? 'edit&id='.$electionID : 'create'));
        exit();
    }

    if (strtotime($endDate) < strtotime($startDate)) {
        $_SESSION['error'] = 'End date must be after start date';
        header('Location: elections.php?action=' . ($electionID ? 'edit&id='.$electionID : 'create'));
        exit();
    }

    try {
        if ($electionID) {
            // Update existing election
            $stmt = $conn->prepare("UPDATE elections SET name = ?, startDate = ?, endDate = ?, status = ? WHERE electionID = ?");
            $stmt->bind_param('ssssi', $name, $startDate, $endDate, $status, $electionID);
            $message = 'Election updated successfully';
        } else {
            // Create new election
            $stmt = $conn->prepare("INSERT INTO elections (name, startDate, endDate, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $name, $startDate, $endDate, $status);
            $message = 'Election created successfully';
        }

        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = $message;
        header('Location: save_election.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: elections.php?action=' . ($electionID ? 'edit&id='.$electionID : 'create'));
        exit();
    }
} else {
    header('Location: elections.php');
    exit();
}
?>