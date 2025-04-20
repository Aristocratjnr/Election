<?php
// Script to fix the votes table constraints
require_once './configs/dbconnection.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing Votes Table Constraints</h1>";

try {
    // First check the constraints on the votes table
    $foreignKeysQuery = "SELECT CONSTRAINT_NAME 
                         FROM information_schema.TABLE_CONSTRAINTS 
                         WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
                         AND TABLE_NAME = 'votes' 
                         AND TABLE_SCHEMA = DATABASE()";
    
    $result = $conn->query($foreignKeysQuery);
    $foreignKeys = [];
    
    while($row = $result->fetch_assoc()) {
        $foreignKeys[] = $row['CONSTRAINT_NAME'];
    }
    
    // Disable foreign key checks to allow modifying constraints
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop foreign key constraints that might be referencing the index
    foreach($foreignKeys as $fkName) {
        $dropFKSQL = "ALTER TABLE votes DROP FOREIGN KEY `$fkName`";
        echo "<p>Dropping foreign key constraint: $fkName</p>";
        $conn->query($dropFKSQL);
    }
    
    // Now try to drop the unique index
    $sql1 = "ALTER TABLE votes DROP INDEX electionID";
    if ($conn->query($sql1)) {
        echo "<p>Successfully dropped existing constraint on votes table.</p>";
    } else {
        echo "<p>Warning: Could not drop the index. It might not exist or have a different name. Proceeding anyway.</p>";
    }
    
    // Add a new unique constraint that allows one vote per student per candidate
    // First check if this constraint already exists
    $checkConstraintSQL = "SHOW INDEXES FROM votes WHERE Key_name = 'election_student_candidate'";
    $constraintCheck = $conn->query($checkConstraintSQL);
    
    if ($constraintCheck->num_rows == 0) {
        $sql2 = "ALTER TABLE votes ADD UNIQUE INDEX election_student_candidate (electionID, studentID, candidateID)";
        if ($conn->query($sql2)) {
            echo "<p>Successfully added new constraint allowing one vote per student per candidate.</p>";
        } else {
            echo "<p>Error adding new constraint: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>The election_student_candidate constraint already exists.</p>";
    }
    
    // Re-create the foreign key constraints
    $createFK1 = "ALTER TABLE votes ADD CONSTRAINT fk_votes_election 
                  FOREIGN KEY (electionID) REFERENCES elections(electionID) ON DELETE CASCADE";
    $createFK2 = "ALTER TABLE votes ADD CONSTRAINT fk_votes_candidate 
                  FOREIGN KEY (candidateID) REFERENCES candidates(candidateID) ON DELETE CASCADE";
    $createFK3 = "ALTER TABLE votes ADD CONSTRAINT fk_votes_student 
                  FOREIGN KEY (studentID) REFERENCES students(studentID) ON DELETE CASCADE";
                  
    $conn->query($createFK1);
    $conn->query($createFK2);
    $conn->query($createFK3);
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<p>Database update completed successfully. The voting system now allows students to vote for multiple positions in the same election.</p>";
    echo "<p><a href='student.php'>Return to Voting Page</a></p>";
    echo "<p><a href='check_votes.php'>Check Vote Records</a></p>";
} catch (Exception $e) {
    // Re-enable foreign key checks even if there was an error
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>