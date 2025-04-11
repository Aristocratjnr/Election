<?php
// Debugging script to check for duplicate candidates
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

echo "<h1>Database Diagnostic for Candidate Duplicates</h1>";

// Check positions table
echo "<h2>All Positions</h2>";
$positions = $conn->query("SELECT positionID, electionID, title FROM positions ORDER BY electionID, display_order, positionID");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Position ID</th><th>Election ID</th><th>Title</th></tr>";
while ($position = $positions->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$position['positionID']}</td>";
    echo "<td>{$position['electionID']}</td>";
    echo "<td>{$position['title']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check candidates table
echo "<h2>All Candidates</h2>";
$candidates = $conn->query("SELECT candidateID, studentID, positionID, status FROM candidates ORDER BY positionID, candidateID");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Candidate ID</th><th>Student ID</th><th>Position ID</th><th>Status</th></tr>";
while ($candidate = $candidates->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$candidate['candidateID']}</td>";
    echo "<td>{$candidate['studentID']}</td>";
    echo "<td>{$candidate['positionID']}</td>";
    echo "<td>{$candidate['status']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check for potential duplicates
echo "<h2>Potential Duplicate Candidates (same student in same position)</h2>";
$duplicates = $conn->query("
    SELECT c1.candidateID as id1, c2.candidateID as id2, c1.studentID, c1.positionID, p.title as position, s.name as student
    FROM candidates c1
    JOIN candidates c2 ON c1.studentID = c2.studentID AND c1.positionID = c2.positionID AND c1.candidateID < c2.candidateID
    JOIN positions p ON c1.positionID = p.positionID
    JOIN students s ON c1.studentID = s.studentID
    ORDER BY c1.positionID, c1.studentID
");

if ($duplicates->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Candidate ID 1</th><th>Candidate ID 2</th><th>Student ID</th><th>Student Name</th><th>Position ID</th><th>Position</th></tr>";
    while ($duplicate = $duplicates->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$duplicate['id1']}</td>";
        echo "<td>{$duplicate['id2']}</td>";
        echo "<td>{$duplicate['studentID']}</td>";
        echo "<td>{$duplicate['student']}</td>";
        echo "<td>{$duplicate['positionID']}</td>";
        echo "<td>{$duplicate['position']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No duplicate candidates found (Same student appearing multiple times for the same position)</p>";
}

// Check for students appearing in multiple positions
echo "<h2>Students in Multiple Positions</h2>";
$multiposition = $conn->query("
    SELECT c.studentID, s.name as student, COUNT(DISTINCT c.positionID) as position_count, 
    GROUP_CONCAT(DISTINCT p.title ORDER BY p.title SEPARATOR ', ') as positions
    FROM candidates c
    JOIN students s ON c.studentID = s.studentID
    JOIN positions p ON c.positionID = p.positionID
    GROUP BY c.studentID
    HAVING COUNT(DISTINCT c.positionID) > 1
    ORDER BY position_count DESC
");

if ($multiposition->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Student ID</th><th>Student Name</th><th>Position Count</th><th>Positions</th></tr>";
    while ($student = $multiposition->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$student['studentID']}</td>";
        echo "<td>{$student['student']}</td>";
        echo "<td>{$student['position_count']}</td>";
        echo "<td>{$student['positions']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No students found in multiple positions</p>";
}

// Check candidates with detailed info
echo "<h2>Candidates with Full Details</h2>";
$details = $conn->query("
    SELECT c.candidateID, c.studentID, s.name as studentName, c.positionID, p.title as positionTitle,
           p.electionID, e.name as electionName, c.status
    FROM candidates c
    JOIN students s ON c.studentID = s.studentID
    JOIN positions p ON c.positionID = p.positionID
    JOIN elections e ON p.electionID = e.electionID
    ORDER BY p.electionID, p.positionID, c.candidateID
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Candidate ID</th><th>Student ID</th><th>Student Name</th><th>Position ID</th><th>Position</th><th>Election ID</th><th>Election</th><th>Status</th></tr>";
while ($detail = $details->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$detail['candidateID']}</td>";
    echo "<td>{$detail['studentID']}</td>";
    echo "<td>{$detail['studentName']}</td>";
    echo "<td>{$detail['positionID']}</td>";
    echo "<td>{$detail['positionTitle']}</td>";
    echo "<td>{$detail['electionID']}</td>";
    echo "<td>{$detail['electionName']}</td>";
    echo "<td>{$detail['status']}</td>";
    echo "</tr>";
}
echo "</table>";
?> 