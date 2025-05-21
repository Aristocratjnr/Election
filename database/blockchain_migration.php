<?php
/**
 * Blockchain Migration Script
 * 
 * This script creates the blockchain_blocks table needed for the blockchain functionality.
 * Run this script once to set up the blockchain infrastructure.
 */

// Include database connection
require_once '../configs/dbconnection.php';

// Check if table exists
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'blockchain_blocks'");
if ($result->num_rows > 0) {
    $tableExists = true;
}

// Create table if it doesn't exist
if (!$tableExists) {
    $sql = "CREATE TABLE IF NOT EXISTS blockchain_blocks (
        block_id INT AUTO_INCREMENT PRIMARY KEY,
        election_id INT NOT NULL,
        previous_hash VARCHAR(64) DEFAULT NULL,
        block_hash VARCHAR(64) NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        nonce INT NOT NULL,
        vote_data TEXT NOT NULL,
        is_valid TINYINT(1) DEFAULT 1,
        INDEX (election_id),
        INDEX (previous_hash)
    ) ENGINE=InnoDB";
    
    if ($conn->query($sql)) {
        echo "Blockchain table created successfully!";
    } else {
        echo "Error creating table: " . $conn->error;
    }
} else {
    echo "Blockchain table already exists!";
}

// Close connection
$conn->close();
?>
