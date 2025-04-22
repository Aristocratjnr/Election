<?php
require 'configs/dbconnection.php';

// Check for candidates with invalid positionIDs
$query = "
    SELECT c.candidateID, c.studentID, c.positionID, p.title AS positionTitle
    FROM candidates c
    LEFT JOIN positions p ON c.positionID = p.positionID
    WHERE p.positionID IS NULL
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h3>Candidates with invalid positionIDs:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "Candidate ID: {$row['candidateID']}, Student ID: {$row['studentID']}, Invalid Position ID: {$row['positionID']}<br>";
    }
} else {
    echo "<h3>No candidates with invalid positionIDs found.</h3>";
}

// Check for mismatches between positionID and position title
$query = "
    SELECT c.candidateID, c.studentID, c.positionID, p.title AS positionTitle
    FROM candidates c
    JOIN positions p ON c.positionID = p.positionID
    WHERE c.positionID != p.positionID
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h3>Mismatches between positionID and position title:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "Candidate ID: {$row['candidateID']}, Student ID: {$row['studentID']}, Position ID: {$row['positionID']}, Position Title: {$row['positionTitle']}<br>";
    }
} else {
    echo "<h3>No mismatches between positionID and position title found.</h3>";
}

// Check for duplicate candidates under the same position
$query = "
    SELECT c.positionID, c.studentID, COUNT(*) AS duplicateCount
    FROM candidates c
    GROUP BY c.positionID, c.studentID
    HAVING duplicateCount > 1
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h3>Duplicate candidates under the same position:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "Position ID: {$row['positionID']}, Student ID: {$row['studentID']}, Duplicate Count: {$row['duplicateCount']}<br>";
    }
} else {
    echo "<h3>No duplicate candidates under the same position found.</h3>";
}

$conn->close();
?>