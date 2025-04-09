<?php
/**
 * Database Schema Update Script
 * 
 * This script adds start_time and end_time columns to the elections table
 * and sets default values for existing records.
 * 
 * Usage: Run this script from the browser or command line to update your database schema.
 */

// Include database connection
require_once 'configs/dbconnection.php';

// Display header
echo "======================================\n";
echo "Election Management System - DB Update\n";
echo "======================================\n\n";

// Check if columns already exist
$column_check = $conn->query("SHOW COLUMNS FROM elections LIKE 'start_time'");
if ($column_check->num_rows > 0) {
    echo "✓ Time columns already exist in the elections table.\n";
    exit;
}

// Define SQL queries
$sql_queries = [
    "ALTER TABLE `elections` ADD COLUMN `start_time` TIME DEFAULT '08:00:00' AFTER `startDate`",
    "ALTER TABLE `elections` ADD COLUMN `end_time` TIME DEFAULT '17:00:00' AFTER `endDate`",
    "UPDATE `elections` SET `start_time` = '08:00:00', `end_time` = '17:00:00'"
];

// Execute each query
$success = true;

echo "Starting database schema update...\n\n";

foreach ($sql_queries as $index => $sql) {
    echo "Executing query " . ($index + 1) . "...\n";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Success!\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
        $success = false;
        break;
    }
}

// Display result
echo "\n";
if ($success) {
    echo "✅ Database schema successfully updated!\n";
    echo "Time columns have been added to the elections table.\n";
} else {
    echo "❌ Database update failed. Please check the errors above.\n";
}

echo "\nDone.\n";
?> 