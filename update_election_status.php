<?php
/**
 * Election Status Updater
 * 
 * This script automatically updates election statuses based on their start and end times:
 * - Elections with current time >= startDate and < endDate become "Ongoing"
 * - Elections with current time >= endDate become "Completed"
 * - Elections with current time < startDate remain "Scheduled"
 * 
 * This can be run:
 * 1. As a scheduled cron job
 * 2. Via AJAX from the admin pages
 * 3. On page load for critical pages like voting and results
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only process if included or directly accessed
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    require_once 'configs/dbconnection.php';
    
    $result = updateElectionStatuses();
    
    // If called directly, return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);
}

/**
 * Update all election statuses based on their time periods
 * 
 * @param mysqli $customConn Optional custom database connection
 * @return array Status information about the update
 */
function updateElectionStatuses($customConn = null) {
    // Use provided connection or global connection
    $conn = $customConn ?? $GLOBALS['conn'];
    
    $result = [
        'success' => false,
        'updated' => 0,
        'ongoing' => 0,
        'completed' => 0,
        'errors' => [],
        'message' => ''
    ];
    
    try {
        // Verify database connection
        if (!$conn || mysqli_connect_errno()) {
            throw new Exception("Database connection failed");
        }
        
        // Get current time in MySQL format
        $currentTime = date('Y-m-d H:i:s');
        
        // Update elections that should be "Ongoing"
        $ongoingStmt = $conn->prepare("
            UPDATE elections 
            SET status = 'Ongoing' 
            WHERE status != 'Ongoing'
            AND ? >= startDate 
            AND ? < endDate
        ");
        
        if (!$ongoingStmt) {
            throw new Exception("Failed to prepare ongoing statement: " . $conn->error);
        }
        
        $ongoingStmt->bind_param("ss", $currentTime, $currentTime);
        
        if (!$ongoingStmt->execute()) {
            throw new Exception("Failed to update ongoing elections: " . $ongoingStmt->error);
        }
        
        $result['ongoing'] = $ongoingStmt->affected_rows;
        $ongoingStmt->close();
        
        // Update elections that should be "Completed"
        $completedStmt = $conn->prepare("
            UPDATE elections 
            SET status = 'Completed' 
            WHERE status != 'Completed'
            AND ? >= endDate
        ");
        
        if (!$completedStmt) {
            throw new Exception("Failed to prepare completed statement: " . $conn->error);
        }
        
        $completedStmt->bind_param("s", $currentTime);
        
        if (!$completedStmt->execute()) {
            throw new Exception("Failed to update completed elections: " . $completedStmt->error);
        }
        
        $result['completed'] = $completedStmt->affected_rows;
        $completedStmt->close();
        
        // Total updated elections
        $result['updated'] = $result['ongoing'] + $result['completed'];
        $result['success'] = true;
        $result['message'] = "Updated " . $result['updated'] . " elections (" . 
                            $result['ongoing'] . " ongoing, " . 
                            $result['completed'] . " completed)";
        
    } catch (Exception $e) {
        $result['success'] = false;
        $result['errors'][] = $e->getMessage();
        $result['message'] = "Error updating election statuses: " . $e->getMessage();
        
        // Log the error
        error_log("Election status update error: " . $e->getMessage());
    }
    
    return $result;
}
?> 