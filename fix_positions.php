<?php
session_start();
require 'configs/dbconnection.php';

// Set proper error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify this is being run by an admin or with proper authorization
// Comment this out if you need to run it directly
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

echo "<h1>Position Deduplication Tool</h1>";

// Function to deduplicate positions
function deduplicatePositions($conn, $electionID = null) {
    echo "<h2>Starting position deduplication process...</h2>";
    
    // Build the SQL to find duplicate positions
    $sql = "
        SELECT e.name as election_name, e.electionID, 
               LOWER(p.title) as lowercase_title, 
               COUNT(*) as position_count,
               GROUP_CONCAT(p.positionID) as position_ids
        FROM positions p
        JOIN elections e ON p.electionID = e.electionID
    ";
    
    // Add election filter if specified
    if ($electionID) {
        $sql .= " WHERE p.electionID = " . intval($electionID);
    }
    
    // Group and filter to find duplicates
    $sql .= "
        GROUP BY e.electionID, LOWER(p.title)
        HAVING COUNT(*) > 1
        ORDER BY e.electionID, lowercase_title
    ";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        echo "<p>No duplicate positions found.</p>";
        return;
    }
    
    echo "<p>Found {$result->num_rows} groups of duplicate positions.</p>";
    
    // Process each set of duplicates
    while ($row = $result->fetch_assoc()) {
        echo "<h3>Processing duplicate positions for '{$row['lowercase_title']}' in election '{$row['election_name']}'</h3>";
        
        // Get the list of position IDs
        $positionIDs = explode(',', $row['position_ids']);
        echo "<p>Position IDs: " . implode(', ', $positionIDs) . "</p>";
        
        // Keep the lowest ID and move all candidates to it
        $keepPositionID = min($positionIDs);
        echo "<p>Keeping position ID: {$keepPositionID}</p>";
        
        // Get details about positions we're merging
        echo "<p>Details of positions being merged:</p>";
        $detailsQuery = "SELECT positionID, title, description, maxVotes, display_order FROM positions WHERE positionID IN (" . implode(',', $positionIDs) . ")";
        $detailsResult = $conn->query($detailsQuery);
        echo "<ul>";
        while ($position = $detailsResult->fetch_assoc()) {
            echo "<li>ID {$position['positionID']}: {$position['title']} (Order: {$position['display_order']}, Max Votes: {$position['maxVotes']})</li>";
        }
        echo "</ul>";
        
        // Move all candidates from other position IDs to the one we're keeping
        $positionsToMerge = array_filter($positionIDs, function($id) use ($keepPositionID) {
            return $id != $keepPositionID;
        });
        
        if (empty($positionsToMerge)) {
            echo "<p>No positions to merge.</p>";
            continue;
        }
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Count candidates before move
            $beforeCount = $conn->query("SELECT COUNT(*) as count FROM candidates WHERE positionID = {$keepPositionID}")->fetch_assoc()['count'];
            echo "<p>Candidates in position {$keepPositionID} before merge: {$beforeCount}</p>";
            
            // For each position being merged, get candidate count
            foreach ($positionsToMerge as $mergeID) {
                $mergeCount = $conn->query("SELECT COUNT(*) as count FROM candidates WHERE positionID = {$mergeID}")->fetch_assoc()['count'];
                echo "<p>Position {$mergeID} has {$mergeCount} candidates to merge</p>";
            }
            
            // Update all candidates to point to the kept position
            $updateSql = "
                UPDATE candidates 
                SET positionID = {$keepPositionID} 
                WHERE positionID IN (" . implode(',', $positionsToMerge) . ")
            ";
            $conn->query($updateSql);
            
            echo "<p>Updated candidate positions.</p>";
            
            // Count candidates after move
            $afterCount = $conn->query("SELECT COUNT(*) as count FROM candidates WHERE positionID = {$keepPositionID}")->fetch_assoc()['count'];
            echo "<p>Candidates in position {$keepPositionID} after merge: {$afterCount}</p>";
            
            // Delete the duplicate positions
            $deleteSql = "
                DELETE FROM positions 
                WHERE positionID IN (" . implode(',', $positionsToMerge) . ")
            ";
            $conn->query($deleteSql);
            
            echo "<p>Deleted duplicate positions: " . implode(', ', $positionsToMerge) . "</p>";
            
            // Commit the transaction
            $conn->commit();
            echo "<p class='success'>Successfully merged positions!</p>";
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            echo "<p class='error'>Error merging positions: {$e->getMessage()}</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<h2>Position deduplication complete!</h2>";
}

// Get list of elections with duplicate positions
$electionsQuery = "
    SELECT DISTINCT e.electionID, e.name
    FROM elections e
    JOIN positions p ON e.electionID = p.electionID
    WHERE EXISTS (
        SELECT 1 
        FROM positions p2 
        WHERE p2.electionID = e.electionID 
        GROUP BY LOWER(p2.title) 
        HAVING COUNT(*) > 1
    )
    ORDER BY e.startDate DESC
";

$electionsResult = $conn->query($electionsQuery);

echo "<h2>Elections with duplicate positions:</h2>";

if ($electionsResult->num_rows == 0) {
    echo "<p>No elections with duplicate positions found.</p>";
} else {
    echo "<ul>";
    while ($election = $electionsResult->fetch_assoc()) {
        echo "<li><a href='?fix=1&election={$election['electionID']}'>{$election['name']} (ID: {$election['electionID']})</a></li>";
    }
    echo "</ul>";
    
    echo "<p><a href='?fix=all' class='button'>Fix ALL duplicate positions</a></p>";
}

// Handle fixing elections
if (isset($_GET['fix'])) {
    if ($_GET['fix'] == 'all') {
        deduplicatePositions($conn);
    } elseif (isset($_GET['election']) && is_numeric($_GET['election'])) {
        deduplicatePositions($conn, intval($_GET['election']));
    }
}

// Add a direct link to go back to positions.php
echo "<p><a href='positions.php'>Go back to Positions Management</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 20px;
    padding: 0;
    color: #333;
}

h1 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

h2 {
    color: #2980b9;
    margin-top: 20px;
}

h3 {
    color: #3498db;
    margin-top: 15px;
}

p {
    margin: 10px 0;
}

ul {
    margin: 10px 0;
}

li {
    margin: 5px 0;
}

a {
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

.button {
    display: inline-block;
    background-color: #3498db;
    color: white;
    padding: 10px 15px;
    border-radius: 4px;
    text-decoration: none;
}

.button:hover {
    background-color: #2980b9;
    text-decoration: none;
}

.success {
    color: #27ae60;
    font-weight: bold;
}

.error {
    color: #e74c3c;
    font-weight: bold;
}

hr {
    border: 0;
    height: 1px;
    background-color: #ddd;
    margin: 20px 0;
}
</style>
