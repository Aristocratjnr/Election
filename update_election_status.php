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
        if (!$conn || mysqli_connect_errno()) {
            throw new Exception("Database connection failed");
        }
        
        // Get current time in MySQL format
        $currentTime = date('Y-m-d H:i:s');
        
        // Only make status updates if this is a scheduled update (not a manual edit)
        // First, log current state
        $debugStmt = $conn->prepare("SELECT electionID, name, startDate, endDate, status FROM elections");
        $debugStmt->execute();
        $elections = $debugStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($elections as $election) {
            error_log("Pre-update: Election {$election['electionID']} ({$election['name']}) - Start: {$election['startDate']}, End: {$election['endDate']}, Status: {$election['status']}");
        }
        
        // Schedule to Ongoing: Only if current time is past start AND before end
        $ongoingStmt = $conn->prepare("
            UPDATE elections 
            SET status = 'Ongoing' 
            WHERE status = 'Scheduled'
            AND DATE_FORMAT(startDate, '%Y-%m-%d %H:%i:%s') <= ?
            AND DATE_FORMAT(endDate, '%Y-%m-%d %H:%i:%s') > ?
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
        
        // Ongoing to Completed: Only if current time is past end date
        $completedStmt = $conn->prepare("
            UPDATE elections 
            SET status = 'Completed' 
            WHERE status = 'Ongoing'
            AND DATE_FORMAT(endDate, '%Y-%m-%d %H:%i:%s') <= ?
        ");
        
        if (!$completedStmt) {
            throw new Exception("Failed to prepare completed statement: " . $conn->error);
        }
        
        $completedStmt->bind_param("s", $currentTime);
        
        if (!$completedStmt->execute()) {
            throw new Exception("Failed to update completed elections: " . $completedStmt->error);
        }
        
        // Log the results after update
        $afterUpdateStmt = $conn->prepare("SELECT electionID, name, startDate, endDate, status FROM elections");
        $afterUpdateStmt->execute();
        $updatedElections = $afterUpdateStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($updatedElections as $election) {
            error_log("Post-update: Election {$election['electionID']} ({$election['name']}) - Start: {$election['startDate']}, End: {$election['endDate']}, New Status: {$election['status']}");
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
        error_log("Election status update error: " . $e->getMessage());
    }
    
    return $result;
}
?>