<?php
/**
 * Cron job script to update election results
 * 
 * This script is designed to be run via cron job to regularly update
 * the election results table. It should be scheduled to run every 
 * 5 minutes for optimal performance.
 * 
 * Example cron entry:
 * # Run every 5 minutes
 * # 5 * * * * php /path/to/your/election/cron_update_results.php
 */

// Disable direct browser access
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Denied: This script can only be run from the command line.');
}

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load dependencies
require_once dirname(__FILE__) . '/configs/dbconnection.php';
require_once dirname(__FILE__) . '/calculate_vote_results.php';

// Log function
function logMessage($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

// Main execution
logMessage('Starting election results update...');

// Update results for all active elections
$result = updateVoteResults($conn);

// Log the result
if ($result['success']) {
    logMessage('Success: ' . $result['message']);
    logMessage('Elections updated: ' . $result['elections_updated']);
    logMessage('Records updated: ' . $result['records_updated']);
} else {
    logMessage('Error: ' . $result['message']);
    if (isset($result['errors']) && is_array($result['errors'])) {
        foreach ($result['errors'] as $error) {
            logMessage('- ' . $error);
        }
    }
}

logMessage('Election results update completed.');
exit($result['success'] ? 0 : 1); 