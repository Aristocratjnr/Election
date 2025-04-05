<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-award me-2"></i>Positions for: <?= htmlspecialchars($election['name']) ?></h5>
                <small class="text-muted">Manage all positions for this election</small>
            </div>
            <a href="?page=positions&action=create&election=<?= $election_id ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Position
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="positionsTable">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Description</th>
                            <th>Max Votes</th>
                            <th>Candidates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, 
                                 (SELECT COUNT(*) FROM candidates 
                                  WHERE positionID = p.positionID) as candidate_count
                                  FROM positions p 
                                  WHERE p.electionID = $election_id
                                  ORDER BY p.order_num";
                        $result = $conn->query($query);
                        
                        while ($position = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($position['name']) ?></td>
                            <td><?= htmlspecialchars($position['description']) ?></td>
                            <td><?= $position['max_votes'] ?></td>
                            <td><?= $position['candidate_count'] ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?page=positions&action=edit&id=<?= $position['positionID'] ?>&election=<?= $election_id ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="?page=candidates&action=manage&position=<?= $position['positionID'] ?>" 
                                       class="btn btn-outline-success">
                                        <i class="bi bi-person-badge"></i>
                                    </a>
                                    <button class="btn btn-outline-danger delete-position" 
                                            data-id="<?= $position['positionID'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#positionsTable').DataTable();
    
    $('.delete-position').click(function() {
        if (confirm('Are you sure you want to delete this position?')) {
            window.location.href = '?page=positions&action=delete&id=' + $(this).data('id') + '&election=<?= $election_id ?>';
        }
    });
});
</script>