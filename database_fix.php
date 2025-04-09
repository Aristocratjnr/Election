<?php
// Database cleanup and fix script
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

echo "<h1>Database Cleanup and Fix</h1>";

// Step 1: Check database structure
echo "<h2>Checking database structure</h2>";

// Check positions table
$positionsCheck = $conn->query("SHOW TABLES LIKE 'positions'");
if ($positionsCheck->num_rows > 0) {
    echo "<p>✅ Positions table exists</p>";
    
    // Check if electionID exists in positions table
    $columnsCheck = $conn->query("SHOW COLUMNS FROM positions LIKE 'electionID'");
    if ($columnsCheck->num_rows > 0) {
        echo "<p>✅ electionID column exists in positions table</p>";
    } else {
        echo "<p>❌ electionID column not found in positions table</p>";
    }
} else {
    echo "<p>❌ Positions table does not exist</p>";
}

// Check candidates table
$candidatesCheck = $conn->query("SHOW TABLES LIKE 'candidates'");
if ($candidatesCheck->num_rows > 0) {
    echo "<p>✅ Candidates table exists</p>";
    
    // Check if positionID exists in candidates table
    $columnsCheck = $conn->query("SHOW COLUMNS FROM candidates LIKE 'positionID'");
    if ($columnsCheck->num_rows > 0) {
        echo "<p>✅ positionID column exists in candidates table</p>";
    } else {
        echo "<p>❌ positionID column not found in candidates table</p>";
    }
} else {
    echo "<p>❌ Candidates table does not exist</p>";
}

// Step 2: Check for invalid references
echo "<h2>Checking for invalid references</h2>";

// Check for candidates with invalid positionID
$invalidPositionCheck = $conn->query("
    SELECT c.candidateID, c.studentID, c.positionID
    FROM candidates c
    LEFT JOIN positions p ON c.positionID = p.positionID
    WHERE p.positionID IS NULL
");

if ($invalidPositionCheck->num_rows > 0) {
    echo "<p>❌ Found candidates with invalid positionID references:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Candidate ID</th><th>Student ID</th><th>Invalid Position ID</th></tr>";
    while ($row = $invalidPositionCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['candidateID']}</td>";
        echo "<td>{$row['studentID']}</td>";
        echo "<td>{$row['positionID']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Fix invalid references
    echo "<p>Fixing invalid references...</p>";
    $conn->query("DELETE FROM candidates WHERE positionID NOT IN (SELECT positionID FROM positions)");
    echo "<p>✅ Invalid candidates removed</p>";
} else {
    echo "<p>✅ No candidates with invalid position references found</p>";
}

// Step 3: Fix database indices and normalization
echo "<h2>Fixing database indices and normalization</h2>";

// Ensure proper indexing on positions table
$conn->query("ALTER TABLE positions ADD INDEX IF NOT EXISTS idx_electionid (electionID)");
echo "<p>✅ Added index on positions.electionID</p>";

// Ensure proper indexing on candidates table
$conn->query("ALTER TABLE candidates ADD INDEX IF NOT EXISTS idx_positionid (positionID)");
$conn->query("ALTER TABLE candidates ADD INDEX IF NOT EXISTS idx_studentid (studentID)");
echo "<p>✅ Added indices on candidates.positionID and candidates.studentID</p>";

// Create a temporary table to identify the correct candidate entries
$conn->query("
CREATE TEMPORARY TABLE IF NOT EXISTS valid_candidates
SELECT MIN(candidateID) as candidateID, studentID, positionID
FROM candidates
GROUP BY studentID, positionID
");
echo "<p>✅ Created temporary table with valid unique candidates</p>";

// Remove any candidate entries not in our valid list
$conn->query("
DELETE FROM candidates 
WHERE candidateID NOT IN (SELECT candidateID FROM valid_candidates)
");
echo "<p>✅ Removed any potential duplicate candidate entries</p>";

// Step 4: Verify the fixes
echo "<h2>Verifying fixes</h2>";

// Check for duplicate candidates one more time
$duplicatesCheck = $conn->query("
SELECT studentID, positionID, COUNT(*) as count
FROM candidates
GROUP BY studentID, positionID
HAVING COUNT(*) > 1
");

if ($duplicatesCheck->num_rows > 0) {
    echo "<p>❌ Still found duplicate candidates:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Student ID</th><th>Position ID</th><th>Count</th></tr>";
    while ($row = $duplicatesCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['studentID']}</td>";
        echo "<td>{$row['positionID']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>✅ No duplicate candidates found after fixes</p>";
}

// Final check - candidates per position
echo "<h2>Current Candidates Per Position</h2>";
$finalCheck = $conn->query("
SELECT p.positionID, p.title as position, COUNT(c.candidateID) as candidate_count
FROM positions p
LEFT JOIN candidates c ON p.positionID = c.positionID
GROUP BY p.positionID
ORDER BY p.positionID
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Position ID</th><th>Position</th><th>Candidate Count</th></tr>";
while ($row = $finalCheck->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['positionID']}</td>";
    echo "<td>{$row['position']}</td>";
    echo "<td>{$row['candidate_count']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>All Fixes Completed</h2>";
 