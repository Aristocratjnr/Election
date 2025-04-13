<?php
// One-time script to fix duplicate positions
require 'configs/dbconnection.php';

echo "<h2>Position Deduplication Script</h2>";

// Find duplicate positions
$sql = "
    SELECT e.electionID, e.name as election_name, 
           LOWER(p.title) as lowercase_title,
           COUNT(*) as position_count,
           GROUP_CONCAT(p.positionID) as position_ids
    FROM positions p
    JOIN elections e ON p.electionID = e.electionID
    GROUP BY e.electionID, LOWER(p.title)
    HAVING COUNT(*) > 1
";

$duplicates = $conn->query($sql);

if ($duplicates->num_rows == 0) {
    echo "<p>No duplicate positions found. All positions are unique.</p>";
} else {
    echo "<p>Found {$duplicates->num_rows} groups of duplicate positions. Fixing...</p>";
    
    while ($row = $duplicates->fetch_assoc()) {
        echo "<h3>Processing duplicate positions for '{$row['lowercase_title']}' in election '{$row['election_name']}'</h3>";
        
        $electionID = $row['electionID'];
        $lowerTitle = $row['lowercase_title'];
        $positionIDs = explode(',', $row['position_ids']);
        
        // Keep the lowest ID and move all candidates to it
        sort($positionIDs, SORT_NUMERIC);
        $keepPositionID = $positionIDs[0];
        $duplicateIDs = array_slice($positionIDs, 1);
        
        echo "<p>Keeping position ID: $keepPositionID</p>";
        echo "<p>Merging from position IDs: " . implode(', ', $duplicateIDs) . "</p>";
        
        // Update candidates from other positions to point to the kept position
        foreach ($duplicateIDs as $duplicateID) {
            $updateStmt = $conn->prepare("
                UPDATE candidates 
                SET positionID = ? 
                WHERE positionID = ?
            ");
            $updateStmt->bind_param('ii', $keepPositionID, $duplicateID);
            $updateStmt->execute();
            $affected = $updateStmt->affected_rows;
            echo "<p>Moved $affected candidates from position ID $duplicateID to $keepPositionID</p>";
            
            // Delete the duplicate position
            $deleteStmt = $conn->prepare("DELETE FROM positions WHERE positionID = ?");
            $deleteStmt->bind_param('i', $duplicateID);
            $deleteStmt->execute();
            echo "<p>Deleted position ID: $duplicateID</p>";
        }
        
        echo "<p>Successfully fixed duplicate position '{$row['lowercase_title']}'</p>";
    }
    
    // Check if we need to recalculate vote results
    echo "<h3>Updating vote results to reflect changes</h3>";
    if (file_exists('calculate_vote_results.php')) {
        include_once 'calculate_vote_results.php';
        $result = updateVoteResults($conn, null); // Update all elections
        echo "<p>Vote results updated: {$result['records_updated']} records affected</p>";
    } else {
        echo "<p>Warning: calculate_vote_results.php not found. Vote counts may need manual updating.</p>";
    }
    
    echo "<p>Position deduplication completed successfully.</p>";
}

echo "<p><a href='live_results.php'>Go to Live Results</a></p>";
?>