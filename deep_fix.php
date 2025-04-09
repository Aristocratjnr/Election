<?php
// Deep fix for position display issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

echo "<h1>Deep Fix for Position Display Issues</h1>";

// Step 1: Examine position numbering and ordering
echo "<h2>Step 1: Examining Position Ordering</h2>";

// Print out all the positions
$positionsQuery = "
    SELECT positionID, title, electionID, description, maxVotes 
    FROM positions 
    ORDER BY electionID, positionID
";
$positions = $conn->query($positionsQuery);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Position ID</th><th>Title</th><th>Election ID</th><th>Description</th><th>Max Votes</th></tr>";
while ($pos = $positions->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$pos['positionID']}</td>";
    echo "<td>{$pos['title']}</td>";
    echo "<td>{$pos['electionID']}</td>";
    echo "<td>{$pos['description']}</td>";
    echo "<td>{$pos['maxVotes']}</td>";
    echo "</tr>";
}
echo "</table>";

// Step 2: Apply fix to ensure correct position numbering
echo "<h2>Step 2: Applying Position Order Fix</h2>";

// First, add a display_order column if it doesn't exist
try {
    $checkColumnQuery = "SHOW COLUMNS FROM positions LIKE 'display_order'";
    $columnExists = $conn->query($checkColumnQuery)->num_rows > 0;
    
    if (!$columnExists) {
        echo "<p>Adding 'display_order' column to positions table...</p>";
        $addColumnQuery = "ALTER TABLE positions ADD COLUMN display_order INT DEFAULT 0";
        if ($conn->query($addColumnQuery)) {
            echo "<p>✅ Successfully added 'display_order' column</p>";
        } else {
            echo "<p>❌ Failed to add 'display_order' column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✅ 'display_order' column already exists</p>";
    }
    
    // Update display_order to match positionID initially
    $updateOrderQuery = "UPDATE positions SET display_order = positionID";
    if ($conn->query($updateOrderQuery)) {
        echo "<p>✅ Updated display_order values</p>";
    } else {
        echo "<p>❌ Failed to update display_order values: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error during column operations: " . $e->getMessage() . "</p>";
}

// Step 3: Fix queries in PHP files to use new ordering
echo "<h2>Step 3: Updating Queries in PHP Files</h2>";

// Student.php fix
try {
    // Read the student.php file
    $studentPhpFile = file_get_contents('student.php');
    
    // Find and replace the positions query
    $oldPositionsQuery = "SELECT positionID, title, description, maxVotes
            FROM positions 
            WHERE electionID = ?
            ORDER BY positionID ASC";
    
    $newPositionsQuery = "SELECT positionID, title, description, maxVotes
            FROM positions 
            WHERE electionID = ?
            ORDER BY display_order, positionID ASC";
    
    $updatedContent = str_replace($oldPositionsQuery, $newPositionsQuery, $studentPhpFile);
    
    if ($studentPhpFile !== $updatedContent) {
        // Write the updated file
        if (file_put_contents('student.php', $updatedContent)) {
            echo "<p>✅ Updated student.php file with new query ordering</p>";
        } else {
            echo "<p>❌ Failed to write updated student.php file</p>";
        }
    } else {
        echo "<p>ℹ️ No changes needed to student.php</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error updating student.php: " . $e->getMessage() . "</p>";
}

// Other files that may need updating
$filesToCheck = ['live_results.php', 'results.php', 'ballots.php'];
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        try {
            $fileContent = file_get_contents($file);
            $positionOrderPattern = '/ORDER BY\s+p\.positionID/i';
            $newPositionOrder = 'ORDER BY p.display_order, p.positionID';
            
            if (preg_match($positionOrderPattern, $fileContent)) {
                $updatedContent = preg_replace($positionOrderPattern, $newPositionOrder, $fileContent);
                if (file_put_contents($file, $updatedContent)) {
                    echo "<p>✅ Updated $file file with new query ordering</p>";
                } else {
                    echo "<p>❌ Failed to write updated $file file</p>";
                }
            } else {
                echo "<p>ℹ️ No position ordering found in $file</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error updating $file: " . $e->getMessage() . "</p>";
        }
    }
}

// Step 4: Cleanup SRC President position display issue
echo "<h2>Step 4: Fixing SRC President Position Issue</h2>";

$duplicatePositionCheck = "
    SELECT positionID, title, electionID 
    FROM positions 
    WHERE title = 'SRC President' 
    AND electionID = 1
";
$duplicatePositions = $conn->query($duplicatePositionCheck);

if ($duplicatePositions->num_rows > 1) {
    echo "<p>Found multiple 'SRC President' positions:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Position ID</th><th>Title</th><th>Election ID</th></tr>";
    
    $positionIds = [];
    while ($pos = $duplicatePositions->fetch_assoc()) {
        $positionIds[] = $pos['positionID'];
        echo "<tr>";
        echo "<td>{$pos['positionID']}</td>";
        echo "<td>{$pos['title']}</td>";
        echo "<td>{$pos['electionID']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Keep the first one, delete the others
    $keepId = $positionIds[0];
    array_shift($positionIds);
    
    echo "<p>Keeping position ID: $keepId</p>";
    echo "<p>Removing position IDs: " . implode(", ", $positionIds) . "</p>";
    
    // Update candidates from the duplicate positions
    foreach ($positionIds as $removeId) {
        $updateCandidatesQuery = "
            UPDATE candidates 
            SET positionID = ? 
            WHERE positionID = ?
        ";
        $stmt = $conn->prepare($updateCandidatesQuery);
        $stmt->bind_param("ii", $keepId, $removeId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        echo "<p>Updated $affectedRows candidates from position ID $removeId to $keepId</p>";
        
        // Delete the duplicate position
        $deletePositionQuery = "DELETE FROM positions WHERE positionID = ?";
        $stmt = $conn->prepare($deletePositionQuery);
        $stmt->bind_param("i", $removeId);
        $stmt->execute();
        $deletedRows = $stmt->affected_rows;
        echo "<p>Deleted position ID $removeId: $deletedRows rows affected</p>";
    }
} else {
    echo "<p>No duplicate 'SRC President' positions found</p>";
}

// Step 5: Check for any leftover duplicate IDs in the table
echo "<h2>Step 5: Checking for any leftover duplicates</h2>";

$finalCheckQuery = "
    SELECT GROUP_CONCAT(positionID) as ids, title, electionID, COUNT(*) as count
    FROM positions
    GROUP BY title, electionID
    HAVING COUNT(*) > 1
";
$finalCheck = $conn->query($finalCheckQuery);

if ($finalCheck->num_rows > 0) {
    echo "<p>⚠️ Still found duplicate positions:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Position IDs</th><th>Title</th><th>Election ID</th><th>Count</th></tr>";
    while ($dup = $finalCheck->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$dup['ids']}</td>";
        echo "<td>{$dup['title']}</td>";
        echo "<td>{$dup['electionID']}</td>";
        echo "<td>{$dup['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p>Please run this script again to fix the remaining duplicates.</p>";
} else {
    echo "<p>✅ No duplicate positions found!</p>";
}

// Final verification
echo "<h2>Final Verification: Current Positions</h2>";
$finalVerifyQuery = "
    SELECT p.positionID, p.title, p.electionID, e.name as electionName, 
           COUNT(c.candidateID) as candidateCount, p.display_order
    FROM positions p
    LEFT JOIN elections e ON p.electionID = e.electionID
    LEFT JOIN candidates c ON p.positionID = c.positionID
    GROUP BY p.positionID
    ORDER BY p.electionID, p.display_order, p.positionID
";
$finalPositions = $conn->query($finalVerifyQuery);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Position ID</th><th>Title</th><th>Election ID</th><th>Election Name</th><th>Candidate Count</th><th>Display Order</th></tr>";
while ($pos = $finalPositions->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$pos['positionID']}</td>";
    echo "<td>{$pos['title']}</td>";
    echo "<td>{$pos['electionID']}</td>";
    echo "<td>{$pos['electionName']}</td>";
    echo "<td>{$pos['candidateCount']}</td>";
    echo "<td>{$pos['display_order']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p>All fixes have been applied. Please refresh your student voting page and check if the issue has been resolved.</p>";
?> 