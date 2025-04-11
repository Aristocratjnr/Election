<?php
// Debug voting page to check for candidate display issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'configs/dbconnection.php';

// Get current election
$stmt = $conn->prepare("
    SELECT * FROM elections 
    WHERE status = 'Ongoing' 
    ORDER BY startDate ASC
    LIMIT 1
");
$stmt->execute();
$currentElection = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Set the election ID
$electionID = $currentElection['electionID'] ?? 0;

// Simple HTML structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Voting Test Page</h1>
        
        <?php if ($electionID > 0): ?>
            <h2>Current Election: <?= htmlspecialchars($currentElection['name']) ?></h2>
            
            <?php
            // Get positions for this election using a simple, direct query
            $positions_query = $conn->prepare("
                SELECT DISTINCT * 
                FROM positions 
                WHERE electionID = ? 
                ORDER BY display_order, positionID ASC
            ");
            $positions_query->bind_param('i', $electionID);
            $positions_query->execute();
            $positions = $positions_query->get_result();
            $positions_query->close();
            ?>
            
            <div class="alert alert-info">
                Found <?= $positions->num_rows ?> positions for this election
            </div>
            
            <form method="post" action="#">
                <?php 
                // Loop through each position
                $position_counter = 1;
                while ($position = $positions->fetch_assoc()): 
                ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Position <?= $position_counter ?>: <?= htmlspecialchars($position['title']) ?></h3>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($position['description'] ?? 'No description') ?></p>
                            <p>Max Votes: <?= (int)$position['maxVotes'] ?></p>
                            
                            <?php
                            // Get candidates for this position using a simple, direct query
                            $candidates_query = $conn->prepare("
                                SELECT DISTINCT c.candidateID, c.studentID, c.photo, c.manifesto, c.status,
                                       s.name, s.department, s.profilePicture
                                FROM candidates c
                                JOIN students s ON c.studentID = s.studentID
                                WHERE c.positionID = ? 
                                AND c.status = 'Approved'
                                ORDER BY s.name ASC
                            ");
                            $candidates_query->bind_param('i', $position['positionID']);
                            $candidates_query->execute();
                            $candidates = $candidates_query->get_result();
                            $candidates_query->close();
                            ?>
                            
                            <div class="alert alert-secondary">
                                Found <?= $candidates->num_rows ?> candidates for position "<?= htmlspecialchars($position['title']) ?>"
                            </div>
                            
                            <div class="row">
                                <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($candidate['name']) ?></h5>
                                                <h6 class="card-subtitle mb-2 text-muted">Department: <?= htmlspecialchars($candidate['department']) ?></h6>
                                                <p class="card-text"><?= htmlspecialchars($candidate['manifesto'] ?? 'No manifesto') ?></p>
                                                
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="position_<?= $position['positionID'] ?>[]" 
                                                           value="<?= $candidate['candidateID'] ?>" 
                                                           id="candidate_<?= $candidate['candidateID'] ?>">
                                                    <label class="form-check-label" for="candidate_<?= $candidate['candidateID'] ?>">
                                                        Select this candidate
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                $position_counter++;
                endwhile; 
                ?>
                
                <button type="submit" class="btn btn-lg btn-primary">Submit Vote</button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                No active election found.
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 