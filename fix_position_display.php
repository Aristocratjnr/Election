<?php
// Quick fix script for position and candidate display issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

echo "<h1>Position and Candidate Display Fix</h1>";

// Check display_order and update if needed
$checkDisplayOrderQuery = "SELECT COUNT(*) as count FROM positions WHERE display_order IS NULL OR display_order = 0";
$result = $conn->query($checkDisplayOrderQuery);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo "<p>Found {$row['count']} positions with missing or zero display_order values. Fixing...</p>";
    
    // Get all elections
    $elections = $conn->query("SELECT electionID, name FROM elections ORDER BY electionID");
    
    while ($election = $elections->fetch_assoc()) {
        echo "<h3>Election: {$election['name']} (ID: {$election['electionID']})</h3>";
        
        // Get positions for this election
        $positions = $conn->query("
            SELECT positionID, title, display_order 
            FROM positions 
            WHERE electionID = {$election['electionID']}
            ORDER BY positionID
        ");
        
        if ($positions->num_rows > 0) {
            echo "<ul>";
            
            // Update display_order for each position
            $order = 1;
            while ($position = $positions->fetch_assoc()) {
                $updateQuery = "UPDATE positions SET display_order = $order WHERE positionID = {$position['positionID']}";
                
                if ($conn->query($updateQuery)) {
                    echo "<li>Updated position: {$position['title']} (ID: {$position['positionID']}) - Old order: " . 
                         ($position['display_order'] ?? 'NULL') . ", New order: $order</li>";
                } else {
                    echo "<li style='color:red'>Failed to update: {$position['title']} - " . $conn->error . "</li>";
                }
                
                $order++;
            }
            
            echo "</ul>";
        } else {
            echo "<p>No positions found for this election.</p>";
        }
    }
} else {
    echo "<p>✅ All positions have valid display_order values.</p>";
}

// Check if positions table has a status column
$hasStatusColumn = false;
$result = $conn->query("DESCRIBE positions");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'status') {
        $hasStatusColumn = true;
        break;
    }
}

if ($hasStatusColumn) {
    // Check for positions with missing or non-approved status
    $query = "SELECT COUNT(*) as count FROM positions WHERE status IS NULL OR status != 'Approved'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<h3>Fixing Position Status</h3>";
        echo "<p>Found {$row['count']} positions with missing or non-approved status. Fixing...</p>";
        
        // Update all positions to have 'Approved' status
        if ($conn->query("UPDATE positions SET status = 'Approved' WHERE status IS NULL OR status != 'Approved'")) {
            echo "<p>✅ Successfully updated all positions to 'Approved' status.</p>";
        } else {
            echo "<p style='color:red'>Failed to update position status: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ All positions have correct status values.</p>";
    }
} else {
    echo "<p>The positions table does not have a 'status' column. No status updates needed.</p>";
}

// Check for missing candidates or candidates with issues
echo "<h3>Checking Candidate Status</h3>";

$query = "
    SELECT p.positionID, p.title, 
           COUNT(c.candidateID) as totalCandidates,
           SUM(CASE WHEN c.status = 'Approved' THEN 1 ELSE 0 END) as approvedCandidates
    FROM positions p
    LEFT JOIN candidates c ON p.positionID = c.positionID
    GROUP BY p.positionID
    HAVING approvedCandidates = 0
    ORDER BY p.positionID
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " positions with no approved candidates:</p>";
    echo "<ul>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<li>Position: {$row['title']} (ID: {$row['positionID']}) - 
            Total candidates: {$row['totalCandidates']}, 
            Approved candidates: {$row['approvedCandidates']}</li>";
            
        // If there are candidates but none are approved, approve them
        if ($row['totalCandidates'] > 0) {
            if ($conn->query("UPDATE candidates SET status = 'Approved' WHERE positionID = {$row['positionID']}")) {
                echo "<li style='color:green'>✅ Approved all candidates for this position.</li>";
            } else {
                echo "<li style='color:red'>Failed to approve candidates: " . $conn->error . "</li>";
            }
        } else {
            echo "<li style='color:orange'>Warning: No candidates found for this position. You should add candidates.</li>";
        }
    }
    
    echo "</ul>";
} else {
    echo "<p>✅ All positions have at least one approved candidate.</p>";
}

// Fix for student.php caching issues
echo "<h3>Clearing Cache</h3>";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>✅ PHP OPcache cleared successfully.</p>";
} else {
    echo "<p>PHP OPcache not available or not enabled.</p>";
}

echo "<p>Don't forget to refresh your browser cache when testing.</p>";

echo "<h3>What to do next:</h3>";
echo "<ol>";
echo "<li>Navigate back to the <a href='positions.php'>Positions page</a> to check if all positions are displaying correctly.</li>";
echo "<li>Navigate to the <a href='student.php'>Student page</a> to check if all positions and candidates are showing properly.</li>";
echo "<li>If you still have issues, try the <a href='diagnose_positions.php'>diagnostic page</a> for more detailed information.</li>";
echo "</ol>";
?> 