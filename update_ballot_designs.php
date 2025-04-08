<?php
require_once 'configs/dbconnection.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('sql/update_ballot_designs.sql');
    
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "Successfully updated ballot_designs table structure.";
    } else {
        throw new Exception("Error executing SQL: " . $conn->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 