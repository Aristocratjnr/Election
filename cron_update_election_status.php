<?php
/**
 * Cron Job Script for Election Status Updates
 *
 * This script is designed to be run regularly via cron job to automatically
 * update election statuses based on their start and end dates. It uses the
 * updateElectionStatuses function from update_election_status.php.
 *
 * Recommended cron schedule: Every 5 minutes
 * Example crontab entry: Run every 5 minutes with: php /path/to/cron_update_election_status.php
 *
 * @version 1.0
 */

// Set execution time limit
set_time_limit(300);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Define log file path
define('LOG_FILE', __DIR__ . '/logs/election_status_updates.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Simple logging function
function logMessage($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message" . PHP_EOL;
    
    // Write to log file
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
    
    // Output to console if run from command line
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

// Log start of execution
logMessage("Starting election status update cron job");

try {
    // Include required files
    require_once __DIR__ . '/configs/dbconnection.php';
    require_once __DIR__ . '/update_election_status.php';
    
    // Run the update function
    $result = updateElectionStatuses();
    
    // Log the results
    if ($result['success']) {
        logMessage("Successfully updated {$result['updated']} elections ({$result['ongoing']} ongoing, {$result['completed']} completed)");
    } else {
        logMessage("Failed to update election statuses: {$result['message']}", 'ERROR');
        foreach ($result['errors'] as $index => $error) {
            logMessage("Error {$index}: {$error}", 'ERROR');
        }
    }

    // Additional functionality: Create notifications for election status changes
    // Get current time in MySQL format including time component
    $currentDateTime = date('Y-m-d H:i:s');
    
    // Update ongoing elections with precise datetime comparison
    $query = "SELECT electionID, name, status, startDate, endDate FROM elections 
              WHERE (status = 'Scheduled' AND TIMESTAMP(startDate) <= TIMESTAMP(?)) 
              OR (status = 'Ongoing' AND TIMESTAMP(endDate) <= TIMESTAMP(?))";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $currentDateTime, $currentDateTime);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch elections: " . $conn->error);
    }
    
    $result = $stmt->get_result();
    
    while ($election = $result->fetch_assoc()) {
        $newStatus = '';
        $notificationTitle = '';
        $notificationMessage = '';
        
        // Determine new status and notification message
        if ($election['status'] === 'Scheduled' && strtotime($election['startDate']) <= time()) {
            $newStatus = 'Ongoing';
            $notificationTitle = "Election Started";
            $notificationMessage = "The election '{$election['name']}' has started. You can now cast your vote!";
        } elseif ($election['status'] === 'Ongoing' && strtotime($election['endDate']) <= time()) {
            $newStatus = 'Completed';
            $notificationTitle = "Election Ended";
            $notificationMessage = "The election '{$election['name']}' has ended. Results will be available soon.";
        }
        
        if ($newStatus) {
            // Update election status
            $updateStmt = $conn->prepare("UPDATE elections SET status = ? WHERE electionID = ?");
            $updateStmt->bind_param("si", $newStatus, $election['electionID']);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Failed to update election status: " . $updateStmt->error);
            }
            
            // Create notifications for all students
            $studentQuery = "SELECT studentID, role FROM students WHERE status = 'Active'";
            $studentResult = $conn->query($studentQuery);
            
            if ($studentResult) {
                $notifyStmt = $conn->prepare("
                    INSERT INTO notifications (user_id, user_type, title, message, type, related_election, is_read, created_at) 
                    VALUES (?, ?, ?, ?, 'election', ?, 0, NOW())
                ");

                while ($student = $studentResult->fetch_assoc()) {
                    $notifyStmt->bind_param(
                        "isssi",
                        $student['studentID'],
                        $student['role'],
                        $notificationTitle,
                        $notificationMessage,
                        $election['electionID']
                    );
                    
                    $notifyStmt->execute();
                }
                
                $notifyStmt->close();
            }
        }
    }
    
    logMessage("Election status update and notifications creation completed successfully.");
    
} catch (Exception $e) {
    // Log any unexpected exceptions
    logMessage("Unexpected error: " . $e->getMessage(), 'ERROR');
    logMessage("Stack trace: " . $e->getTraceAsString(), 'ERROR');
}

// Log end of execution
logMessage("Finished election status update cron job");

$conn->close();
?>
