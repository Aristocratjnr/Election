<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="?page=election_results&action=view&election=<?= $election_id ?>">
                        <i class="bi bi-table me-1"></i> Summary
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=election_results&action=live&election=<?= $election_id ?>">
                        <i class="bi bi-speedometer2 me-1"></i> Live Results
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=election_results&action=reports&election=<?= $election_id ?>">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=election_results&action=analytics&election=<?= $election_id ?>">
                        <i class="bi bi-pie-chart me-1"></i> Analytics
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <h4 class="mb-4">Election Results: <?= htmlspecialchars($election['name']) ?></h4>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Votes Cast</h6>
                            <?php
                            $total_votes = $conn->query("
                                SELECT COUNT(DISTINCT studentID) as total 
                                FROM votes 
                                WHERE electionID = $election_id
                            ")->fetch_assoc()['total'];
                            ?>
                            <h2 class="text-primary"><?= $total_votes ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Registered Voters</h6>
                            <?php
                            $total_voters = $conn->query("
                                SELECT COUNT(*) as total 
                                FROM students 
                                WHERE status = 'Active'
                            ")->fetch_assoc()['total'];
                            ?>
                            <h2 class="text-primary"><?= $total_voters ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Participation Rate</h6>
                            <?php
                            $participation = ($total_voters > 0) ? round(($total_votes / $total_voters) * 100) : 0;
                            ?>
                            <h2 class="text-primary"><?= $participation ?>%</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
            // Get all positions for this election
            $positions = $conn->query("
                SELECT * FROM positions 
                WHERE electionID = $election_id
                ORDER BY order_num
            ");
            
            while ($position = $positions->fetch_assoc()):
                // Get candidates and their vote counts
                $candidates = $conn->query("
                    SELECT c.*, 
                    (SELECT COUNT(*) FROM votes 
                     WHERE candidateID = c.candidateID) as vote_count
                    FROM candidates c
                    WHERE c.positionID = {$position['positionID']}
                    ORDER BY vote_count DESC
                ");
            ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5><?= htmlspecialchars($position['name']) ?></h5>
                    <p class="mb-0 text-muted"><?= htmlspecialchars($position['description']) ?></p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Votes</th>
                                    <th>Percentage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_position_votes = $conn->query("
                                    SELECT COUNT(*) as total 
                                    FROM votes v
                                    JOIN candidates c ON v.candidateID = c.candidateID
                                    WHERE c.positionID = {$position['positionID']}
                                ")->fetch_assoc()['total'];
                                
                                while ($candidate = $candidates->fetch_assoc()): 
                                    $percentage = ($total_position_votes > 0) ? 
                                        round(($candidate['vote_count'] / $total_position_votes) * 100) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?>
                                    </td>
                                    <td><?= $candidate['vote_count'] ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: <?= $percentage ?>%">
                                                <?= $percentage ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($position['status'] == 'Completed'): ?>
                                            <?php 
                                                $allCandidates = $candidates->fetch_all(MYSQLI_ASSOC);
                                                $maxVotes = !empty($allCandidates) ? max(array_column($allCandidates, 'vote_count')) : 0;
                                                $isWinner = ($candidate['vote_count'] == $maxVotes && $maxVotes > 0);
                                            ?>
                                            <span class="badge bg-<?= $isWinner ? 'success' : 'secondary' ?>">
                                                <?= $isWinner ? 'Winner' : 'Candidate' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Running</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            
            <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-primary me-2">
                    <i class="bi bi-download me-1"></i> Export Results
                </button>
                <button class="btn btn-success">
                    <i class="bi bi-printer me-1"></i> Print Report
                </button>
            </div>
        </div>
    </div>
</div>