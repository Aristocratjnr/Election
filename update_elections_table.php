<?php
require_once 'configs/dbconnection.php';

try {
    // Add visibility column if it doesn't exist
    $sql = "ALTER TABLE elections 
            ADD COLUMN IF NOT EXISTS visibility ENUM('Public', 'Private') 
            DEFAULT 'Public' 
            AFTER status";
    
    if ($conn->query($sql)) {
        echo "Successfully added visibility column to elections table";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 