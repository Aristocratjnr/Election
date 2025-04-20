<?php
// Script to fix the votes table constraints
require_once './configs/dbconnection.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Updating Votes Table Structure</h1>";

try {
    // First drop the existing constraint
    $sql1 = "ALTER TABLE votes DROP INDEX electionID";
    if ($conn->query($sql1)) {
        echo "<p>Successfully dropped existing constraint on votes table.</p>";
    } else {
        echo "<p>Error dropping constraint: " . $conn->error . "</p>";
    }

    // Add a new constraint that allows one vote per student per position
    $sql2 = "ALTER TABLE votes ADD UNIQUE INDEX election_student_position (electionID, studentID, candidateID)";
    if ($conn->query($sql2)) {
        echo "<p>Successfully added new constraint allowing one vote per student per position.</p>";
    } else {
        echo "<p>Error adding new constraint: " . $conn->error . "</p>";
    }

    echo "<p>Database update completed. You may now delete this file.</p>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>