<?php
// Script to fix duplicate positions in the database
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

echo "<h1>Position Deduplication Tool</h1>";

// Step 1: Identify duplicate positions
echo "<h2>Step 1: Identifying duplicate positions</h2>";
$duplicatePositionsQuery = "
    SELECT title, electionID, COUNT(*) as count, GROUP_CONCAT(positionID ORDER BY positionID ASC) as positionIDs
    FROM positions
    GROUP BY title, electionID
    HAVING COUNT(*) > 1
";
$duplicatePositions = $conn->query($duplicatePositionsQuery);

if ($duplicatePositions->num_rows > 0) {
    echo "<p>Found duplicate position titles in the same election:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Position Title</th><th>Election ID</th><th>Count</th><th>Position IDs</th></tr>";
    
    // Store duplicates for processing
    $duplicatesToFix = [];
    
    while ($dup = $duplicatePositions->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$dup['title']}</td>";
        echo "<td>{$dup['electionID']}</td>";
        echo "<td>{$dup['count']}</td>";
        echo "<td>{$dup['positionIDs']}</td>";
        echo "</tr>";
        
        // Add to processing list
        $duplicatesToFix[] = [
            'title' => $dup['title'],
            'electionID' => $dup['electionID'],
            'positionIDs' => explode(',', $dup['positionIDs'])
        ];
    }
    echo "</table>";
    
    // Step 2: Fix duplicate positions
    echo "<h2>Step 2: Fixing duplicate positions</h2>";
    
    foreach ($duplicatesToFix as $dup) {
        echo "<h3>Processing duplicates for '{$dup['title']}' in election ID {$dup['electionID']}</h3>";
        
        // Get all position IDs
        $positionIDs = $dup['positionIDs'];
        
        // Keep the first position ID (lowest one)
        $keepPositionID = $positionIDs[0];
        
        // Remove other position IDs
        array_shift($positionIDs); // Remove first element that we're keeping
        $removePositionIDs = $positionIDs;
        
        echo "<p>Keeping position ID: $keepPositionID</p>";
        echo "<p>Removing position IDs: " . implode(', ', $removePositionIDs) . "</p>";
        
        // Update candidates to use the kept position ID
        foreach ($removePositionIDs as $removeID) {
            // Move candidates from duplicate position to the kept position
            $updateCandidatesQuery = "
                UPDATE candidates
                SET positionID = ?
                WHERE positionID = ?
            ";
            $stmt = $conn->prepare($updateCandidatesQuery);
            $stmt->bind_param('ii', $keepPositionID, $removeID);
            $stmt->execute();
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            echo "<p>Updated $affectedRows candidates from position ID $removeID to position ID $keepPositionID</p>";
            
            // Delete the duplicate position
            $deletePositionQuery = "
                DELETE FROM positions
                WHERE positionID = ?
            ";
            $stmt = $conn->prepare($deletePositionQuery);
            $stmt->bind_param('i', $removeID);
            $stmt->execute();
            $deletedRows = $stmt->affected_rows;
            $stmt->close();
            
            echo "<p>Deleted position ID $removeID: $deletedRows rows affected</p>";
        }
    }
    
    echo "<h2>Position deduplication complete!</h2>";
    echo "<p>All duplicate positions have been resolved. Candidates have been assigned to the correct positions.</p>";
    
} else {
    echo "<p>No duplicate positions found in the database.</p>";
}

// Step 3: Verify the fix
echo "<h2>Verification: Current Positions</h2>";
$verifyQuery = "
    SELECT p.positionID, p.title, p.electionID, e.name as electionName, COUNT(c.candidateID) as candidateCount
    FROM positions p
    LEFT JOIN elections e ON p.electionID = e.electionID
    LEFT JOIN candidates c ON p.positionID = c.positionID
    GROUP BY p.positionID
    ORDER BY p.electionID, p.positionID
";
$positions = $conn->query($verifyQuery);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Position ID</th><th>Title</th><th>Election ID</th><th>Election Name</th><th>Candidate Count</th></tr>";
while ($pos = $positions->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$pos['positionID']}</td>";
    echo "<td>{$pos['title']}</td>";
    echo "<td>{$pos['electionID']}</td>";
    echo "<td>{$pos['electionName']}</td>";
    echo "<td>{$pos['candidateCount']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p>Please refresh your student voting page and check if the issue has been resolved.</p>";
?> 