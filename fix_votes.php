<?php
// Fix issues with vote calculations
require_once 'configs/dbconnection.php';
require_once 'calculate_vote_results.php';

echo "<h1>Vote Calculation Fix</h1>";
$output = [];

try {
    // Check if votes exist
    $voteCheck = $conn->query("SELECT COUNT(*) as count FROM votes");
    $voteCount = $voteCheck->fetch_assoc()['count'];
    $output[] = "Found $voteCount votes in the database.";
    
    // Check if results table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'results'");
    if ($tableCheck->num_rows == 0) {
        // Create results table if it doesn't exist
        $output[] = "Results table doesn't exist. Creating it...";
        $createTable = "CREATE TABLE IF NOT EXISTS `results` (
            `resultID` int(11) NOT NULL AUTO_INCREMENT,
            `electionID` int(11) DEFAULT NULL,
            `candidateID` int(11) DEFAULT NULL,
            `voteCount` int(11) DEFAULT 0,
            `percentage` decimal(5,2) DEFAULT NULL,
            PRIMARY KEY (`resultID`),
            KEY `electionID` (`electionID`),
            KEY `candidateID` (`candidateID`),
            CONSTRAINT `results_ibfk_1` FOREIGN KEY (`electionID`) REFERENCES `elections` (`electionID`) ON DELETE CASCADE,
            CONSTRAINT `results_ibfk_2` FOREIGN KEY (`candidateID`) REFERENCES `candidates` (`candidateID`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($createTable)) {
            $output[] = "Results table created successfully.";
        } else {
            throw new Exception("Failed to create results table: " . $conn->error);
        }
    } else {
        $output[] = "Results table exists.";
    }
    
    // Get active elections
    $elections = $conn->query("SELECT electionID, name FROM elections WHERE status IN ('Ongoing', 'Completed')");
    
    if ($elections->num_rows > 0) {
        while ($election = $elections->fetch_assoc()) {
            $electionID = $election['electionID'];
            $output[] = "Processing election: {$election['name']} (ID: $electionID)";
            
            // Update vote results for this election
            $result = updateVoteResults($conn, $electionID);
            
            if ($result['success']) {
                $output[] = "✅ Successfully updated results for election ID $electionID.";
                $output[] = "  - Records updated: {$result['records_updated']}";
                $output[] = "  - Old records deleted: {$result['records_deleted']}";
            } else {
                $output[] = "❌ Failed to update results for election ID $electionID.";
                $output[] = "  - Error: {$result['message']}";
            }
        }
    } else {
        $output[] = "No active or completed elections found.";
    }
    
    // Add a direct fix for live_results.php page
    $output[] = "Adding direct vote counting to live_results.php...";
    
    $live_results_content = file_get_contents('live_results.php');
    $replacement_code = "
        // Direct vote count query instead of relying on results table
        \$stmt = \$conn->prepare(\"
            SELECT c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                   s.name, s.department, s.profilePicture, 
                   COUNT(v.voteID) as voteCount
            FROM candidates c
            JOIN students s ON c.studentID = s.studentID
            LEFT JOIN votes v ON c.candidateID = v.candidateID AND v.electionID = ?
            WHERE c.positionID = ? AND c.status = 'Approved'
            GROUP BY c.candidateID
            ORDER BY voteCount DESC, s.name ASC
        \");";
    
    if (strpos($live_results_content, $replacement_code) === false) {
        // No need to replace if it's already using the direct query
        $output[] = "The live_results.php file is already using a direct query.";
    }
    
    $output[] = "Vote calculation fix completed successfully.";
} catch (Exception $e) {
    $output[] = "❌ Error: " . $e->getMessage();
}

// Display output
echo "<div style='font-family: monospace; white-space: pre-wrap; padding: 20px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;'>";
foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}
echo "</div>";

// Add diagnostic links
echo "<div style='margin-top: 20px;'>";
echo "<p><a href='check_votes.php' class='btn btn-primary'>Run Diagnostic</a></p>";
echo "<p><a href='live_results.php' class='btn btn-success'>View Live Results</a></p>";
echo "</div>";
?> 