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
} catch (Exception $e) {
    // Log any unexpected exceptions
    logMessage("Unexpected error: " . $e->getMessage(), 'ERROR');
    logMessage("Stack trace: " . $e->getTraceAsString(), 'ERROR');
}

// Log end of execution
logMessage("Finished election status update cron job");
