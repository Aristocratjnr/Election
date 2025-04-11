<?php
// Check votes and results tables
require_once 'configs/dbconnection.php';

echo "<h1>Vote System Diagnostic</h1>";

// Check votes table
echo "<h2>Votes Table</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM votes");
$row = $result->fetch_assoc();
echo "<p>Total votes: " . $row['count'] . "</p>";

if ($row['count'] > 0) {
    $result = $conn->query("SELECT v.*, c.positionID, s.name as studentName, CONCAT(p.title, ' (', e.name, ')') as positionElection
                            FROM votes v
                            JOIN candidates c ON v.candidateID = c.candidateID
                            JOIN students s ON v.studentID = s.studentID
                            JOIN positions p ON c.positionID = p.positionID
                            JOIN elections e ON v.electionID = e.electionID
                            ORDER BY v.timestamp DESC
                            LIMIT 20");
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Vote ID</th><th>Election</th><th>Position</th><th>Student</th><th>Timestamp</th></tr>";
    
    while ($vote = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $vote['voteID'] . "</td>";
        echo "<td>" . $vote['electionID'] . "</td>";
        echo "<td>" . $vote['positionElection'] . "</td>";
        echo "<td>" . $vote['studentName'] . " (ID: " . $vote['studentID'] . ")</td>";
        echo "<td>" . $vote['timestamp'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Check results table
echo "<h2>Results Table</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM results");
$row = $result->fetch_assoc();
echo "<p>Total result records: " . $row['count'] . "</p>";

if ($row['count'] > 0) {
    $result = $conn->query("SELECT r.*, e.name as electionName, s.name as studentName, p.title as positionTitle
                            FROM results r
                            JOIN candidates c ON r.candidateID = c.candidateID
                            JOIN students s ON c.studentID = s.studentID
                            JOIN positions p ON c.positionID = p.positionID
                            JOIN elections e ON r.electionID = e.electionID
                            ORDER BY r.electionID, p.positionID, r.voteCount DESC
                            LIMIT 20");
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Result ID</th><th>Election</th><th>Position</th><th>Candidate</th><th>Vote Count</th><th>Percentage</th></tr>";
    
    while ($result_row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $result_row['resultID'] . "</td>";
        echo "<td>" . $result_row['electionName'] . " (ID: " . $result_row['electionID'] . ")</td>";
        echo "<td>" . $result_row['positionTitle'] . "</td>";
        echo "<td>" . $result_row['studentName'] . " (ID: " . $result_row['candidateID'] . ")</td>";
        echo "<td>" . $result_row['voteCount'] . "</td>";
        echo "<td>" . $result_row['percentage'] . "%</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} 

// Check table structures
echo "<h2>Table Structures</h2>";

echo "<h3>Votes Table Structure</h3>";
$result = $conn->query("DESCRIBE votes");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($field = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $field['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Results Table Structure</h3>";
$result = $conn->query("DESCRIBE results");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($field = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . $field['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check the calculate_vote_results.php script
echo "<h2>Testing Vote Calculation</h2>";
if (file_exists('calculate_vote_results.php')) {
    echo "<p>calculate_vote_results.php exists. Running test...</p>";
    
    require_once 'calculate_vote_results.php';
    
    // Get a sample election
    $result = $conn->query("SELECT electionID FROM elections WHERE status IN ('Ongoing', 'Completed') LIMIT 1");
    if ($result->num_rows > 0) {
        $electionID = $result->fetch_assoc()['electionID'];
        echo "<p>Testing with election ID: " . $electionID . "</p>";
        
        // Run the calculation
        $testResult = updateVoteResults($conn, $electionID);
        
        echo "<pre>";
        print_r($testResult);
        echo "</pre>";
    } else {
        echo "<p>No ongoing or completed elections found for testing.</p>";
    }
} else {
    echo "<p>ERROR: calculate_vote_results.php does not exist!</p>";
}

echo "<hr>";
echo "<p>Diagnostic completed at " . date('Y-m-d H:i:s') . "</p>";
?> 