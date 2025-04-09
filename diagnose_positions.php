<?php
// Diagnostic script to help debug position/candidate display issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

echo "<h1>Position and Candidate Diagnostic</h1>";

// Get all positions
echo "<h2>All Positions</h2>";
$positions = $conn->query("
    SELECT p.*, e.name as electionName 
    FROM positions p
    JOIN elections e ON p.electionID = e.electionID
    ORDER BY e.electionID, p.display_order, p.positionID
");

echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>Position ID</th>
        <th>Election ID</th>
        <th>Election Name</th>
        <th>Title</th>
        <th>Description</th>
        <th>Max Votes</th>
        <th>Display Order</th>
        <th>Created At</th>
        <th>Status</th>
      </tr>";

while ($position = $positions->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$position['positionID']}</td>";
    echo "<td>{$position['electionID']}</td>";
    echo "<td>{$position['electionName']}</td>";
    echo "<td>{$position['title']}</td>";
    echo "<td>{$position['description']}</td>";
    echo "<td>{$position['maxVotes']}</td>";
    echo "<td>" . ($position['display_order'] ?? 'NULL') . "</td>";
    echo "<td>" . ($position['created_at'] ?? 'NULL') . "</td>";
    echo "<td>" . ($position['status'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Get all candidates
echo "<h2>All Candidates</h2>";
$candidates = $conn->query("
    SELECT c.*, s.name as studentName, p.title as positionTitle, e.name as electionName 
    FROM candidates c
    JOIN students s ON c.studentID = s.studentID
    LEFT JOIN positions p ON c.positionID = p.positionID
    LEFT JOIN elections e ON p.electionID = e.electionID
    ORDER BY c.positionID, c.candidateID
");

echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>Candidate ID</th>
        <th>Student ID</th>
        <th>Student Name</th>
        <th>Position ID</th>
        <th>Position</th>
        <th>Election</th>
        <th>Status</th>
        <th>Created At</th>
      </tr>";

while ($candidate = $candidates->fetch_assoc()) {
    $rowClass = $candidate['status'] === 'Approved' ? 'style="background-color:#d4edda;"' : '';
    
    echo "<tr $rowClass>";
    echo "<td>{$candidate['candidateID']}</td>";
    echo "<td>{$candidate['studentID']}</td>";
    echo "<td>{$candidate['studentName']}</td>";
    echo "<td>{$candidate['positionID']}</td>";
    echo "<td>{$candidate['positionTitle']}</td>";
    echo "<td>{$candidate['electionName']}</td>";
    echo "<td>{$candidate['status']}</td>";
    echo "<td>" . ($candidate['created_at'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Now check how the student.php page would see positions/candidates
echo "<h2>What student.php Would See</h2>";

// Simulate student.php logic
$currentElection = null;
try {
    // Fetch current election
    $stmt = $conn->prepare("
        SELECT * FROM elections 
        WHERE status = 'Ongoing' 
        OR (status = 'Scheduled' AND startDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
        ORDER BY startDate ASC
        LIMIT 1
    ");
    $stmt->execute();
    $currentElection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($currentElection) {
        echo "<p>Current Election: <strong>{$currentElection['name']}</strong> (ID: {$currentElection['electionID']})</p>";
        
        // Get positions for current election
        $stmt = $conn->prepare("
            SELECT positionID, title, description, maxVotes
            FROM positions 
            WHERE electionID = ?
            ORDER BY display_order, positionID ASC
        ");
        $stmt->bind_param('i', $currentElection['electionID']);
        $stmt->execute();
        $positions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        echo "<p>Found " . count($positions) . " positions for this election</p>";
        
        echo "<div style='margin-bottom: 20px;'>";
        foreach ($positions as $position) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;'>";
            echo "<h3>{$position['title']} (ID: {$position['positionID']})</h3>";
            echo "<p>{$position['description']}</p>";
            echo "<p>Max Votes: {$position['maxVotes']}</p>";
            
            // Get candidates for this position 
            $stmt = $conn->prepare("
                SELECT c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                       s.name, s.department, s.profilePicture
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.positionID = ? 
                AND c.status = 'Approved'
                ORDER BY s.name ASC
            ");
            $stmt->bind_param('i', $position['positionID']);
            $stmt->execute();
            $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            echo "<p style='font-weight: bold;'>Found " . count($candidates) . " approved candidates for this position:</p>";
            
            if (count($candidates) > 0) {
                echo "<ul>";
                foreach ($candidates as $candidate) {
                    echo "<li>{$candidate['name']} (ID: {$candidate['candidateID']}, Dept: {$candidate['department']})</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>No approved candidates found for this position!</p>";
            }
            
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No current election found!</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Check for positions with no candidates or no approved candidates
echo "<h2>Positions with No Approved Candidates</h2>";
$sql = "
    SELECT p.positionID, p.title, p.electionID, e.name as electionName,
           COUNT(c.candidateID) as totalCandidates,
           SUM(CASE WHEN c.status = 'Approved' THEN 1 ELSE 0 END) as approvedCandidates
    FROM positions p
    LEFT JOIN candidates c ON p.positionID = c.positionID
    JOIN elections e ON p.electionID = e.electionID
    GROUP BY p.positionID
    ORDER BY approvedCandidates ASC, p.electionID, p.display_order
";
$result = $conn->query($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>Position ID</th>
        <th>Title</th>
        <th>Election</th>
        <th>Total Candidates</th>
        <th>Approved Candidates</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    $rowClass = $row['approvedCandidates'] == 0 ? 'style="background-color:#f8d7da;"' : '';
    echo "<tr $rowClass>";
    echo "<td>{$row['positionID']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>{$row['electionName']} (ID: {$row['electionID']})</td>";
    echo "<td>{$row['totalCandidates']}</td>";
    echo "<td>{$row['approvedCandidates']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check for db structure
echo "<h2>Database Structure Check</h2>";

echo "<h3>positions Table Structure</h3>";
$result = $conn->query("DESCRIBE positions");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>candidates Table Structure</h3>";
$result = $conn->query("DESCRIBE candidates");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Fix missing display_order values
echo "<h2>Fixing Missing display_order Values</h2>";

$checkNullQuery = "SELECT COUNT(*) as count FROM positions WHERE display_order IS NULL OR display_order = 0";
$result = $conn->query($checkNullQuery);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo "<p>Found {$row['count']} positions with missing display_order values. Fixing...</p>";
    
    // Update each election separately to keep ordering by election
    $electionsWithIssues = $conn->query("
        SELECT DISTINCT e.electionID, e.name 
        FROM positions p
        JOIN elections e ON p.electionID = e.electionID
        WHERE p.display_order IS NULL OR p.display_order = 0
    ");
    
    while ($election = $electionsWithIssues->fetch_assoc()) {
        echo "<p>Fixing positions for election: {$election['name']} (ID: {$election['electionID']})</p>";
        
        // Get all positions for this election
        $positionsQuery = $conn->prepare("
            SELECT positionID, title 
            FROM positions 
            WHERE electionID = ? 
            ORDER BY positionID
        ");
        $positionsQuery->bind_param('i', $election['electionID']);
        $positionsQuery->execute();
        $positions = $positionsQuery->get_result();
        
        // Update display_order values starting from 1
        $order = 1;
        while ($position = $positions->fetch_assoc()) {
            $updateQuery = $conn->prepare("UPDATE positions SET display_order = ? WHERE positionID = ?");
            $updateQuery->bind_param('ii', $order, $position['positionID']);
            $updateQuery->execute();
            echo "<p>Updated position ID {$position['positionID']} ({$position['title']}) with display_order = {$order}</p>";
            $order++;
        }
    }
    
    echo "<p>All display_order values have been fixed.</p>";
} else {
    echo "<p>No issues found with display_order values.</p>";
}

// Check for positions with status column issues
echo "<h2>Checking Position Status Values</h2>";

// Check if positions has a status column
$hasStatusColumn = false;
$result = $conn->query("DESCRIBE positions");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'status') {
        $hasStatusColumn = true;
        break;
    }
}

if ($hasStatusColumn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM positions WHERE status != 'Approved' AND status IS NOT NULL");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<p>Found {$row['count']} positions with non-Approved status. This could be affecting display.</p>";
        
        $positions = $conn->query("SELECT positionID, title, status FROM positions WHERE status != 'Approved' AND status IS NOT NULL");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Position ID</th><th>Title</th><th>Status</th></tr>";
        while ($position = $positions->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$position['positionID']}</td>";
            echo "<td>{$position['title']}</td>";
            echo "<td>{$position['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p>Do you want to fix these positions? Add ?fix_status=1 to the URL.</p>";
        
        if (isset($_GET['fix_status']) && $_GET['fix_status'] == 1) {
            $conn->query("UPDATE positions SET status = 'Approved' WHERE status != 'Approved' OR status IS NULL");
            echo "<p>All positions have been updated to 'Approved' status.</p>";
        }
    } else {
        echo "<p>All positions have correct status values.</p>";
    }
} else {
    echo "<p>The positions table does not have a status column. No action needed.</p>";
}
?> 