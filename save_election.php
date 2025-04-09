<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth_check.php';
require_once 'configs/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the incoming request for debugging
    error_log('save_election.php - POST data: ' . print_r($_POST, true));
    
    $action = $_POST['action'] ?? '';
    $name = $_POST['name'] ?? '';
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    $status = $_POST['status'] ?? '';
    $visibility = $_POST['visibility'] ?? 'Public';
    
    // Validate inputs
    if (empty($name) || empty($startDate) || empty($endDate) || empty($status)) {
        error_log('save_election.php - Missing required fields');
        header('Location: election.php?error=missing_fields');
        exit;
    }
    
    try {
        // Format dates
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        if ($startTimestamp === false || $endTimestamp === false) {
            error_log('save_election.php - Invalid date format: Start=' . $startDate . ', End=' . $endDate);
            throw new Exception("Invalid date format");
        }
        
        $formattedStartDate = date('Y-m-d H:i:s', $startTimestamp);
        $formattedEndDate = date('Y-m-d H:i:s', $endTimestamp);
        
        // Check dates
        if ($formattedEndDate < $formattedStartDate) {
            error_log('save_election.php - Invalid date range: End date before start date');
            header('Location: election.php?error=invalid_dates');
            exit;
        }
        
        if ($action === 'edit' && isset($_POST['electionID'])) {
            $electionID = (int)$_POST['electionID'];
            error_log('save_election.php - Updating election ID: ' . $electionID);
            
            // Update existing election
            $stmt = $conn->prepare("
                UPDATE elections 
                SET name = ?, startDate = ?, endDate = ?, status = ?, visibility = ?
                WHERE electionID = ?
            ");
            
            if (!$stmt) {
                error_log('save_election.php - Prepare failed: ' . $conn->error);
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("sssssi", 
                $name, 
                $formattedStartDate, 
                $formattedEndDate, 
                $status, 
                $visibility,
                $electionID
            );
            
            if (!$stmt->execute()) {
                error_log('save_election.php - Execute failed: ' . $stmt->error);
                throw new Exception("Failed to execute update: " . $stmt->error);
            }
            
            error_log('save_election.php - Update successful. Affected rows: ' . $stmt->affected_rows);
            $stmt->close();
            header('Location: election.php?success=updated');
            exit;
        } else {
            error_log('save_election.php - Creating new election');
            
            // Create new election
            $stmt = $conn->prepare("
                INSERT INTO elections (name, startDate, endDate, status, visibility)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                error_log('save_election.php - Prepare failed: ' . $conn->error);
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", 
                $name, 
                $formattedStartDate, 
                $formattedEndDate, 
                $status, 
                $visibility
            );
            
            if (!$stmt->execute()) {
                error_log('save_election.php - Execute failed: ' . $stmt->error);
                throw new Exception("Failed to execute insert: " . $stmt->error);
            }
            
            $newElectionID = $conn->insert_id;
            error_log('save_election.php - New election created with ID: ' . $newElectionID);
            
            $stmt->close();
            header('Location: election.php?success=created');
            exit;
        }
    } catch (Exception $e) {
        error_log('save_election.php - Exception: ' . $e->getMessage());
        header('Location: election.php?error=db_error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

// If accessed directly without POST data
error_log('save_election.php - Direct access without POST data');
header('Location: election.php');
exit;
?>