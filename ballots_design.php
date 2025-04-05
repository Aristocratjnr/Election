<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Ballot Designer: <?= htmlspecialchars($election['name']) ?></h5>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-2" id="previewBallot">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
                <button class="btn btn-sm btn-primary" id="saveDesign">
                    <i class="bi bi-save me-1"></i> Save Design
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Design Elements</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary design-element" data-type="header">
                                    <i class="bi bi-type-h1 me-1"></i> Header
                                </button>
                                <button class="btn btn-outline-primary design-element" data-type="text">
                                    <i class="bi bi-text-paragraph me-1"></i> Text Block
                                </button>
                                <button class="btn btn-outline-primary design-element" data-type="position">
                                    <i class="bi bi-award me-1"></i> Position
                                </button>
                                <button class="btn btn-outline-primary design-element" data-type="image">
                                    <i class="bi bi-image me-1"></i> Image
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Templates</h6>
                        </div>
                        <div class="card-body">
                            <select class="form-select mb-3" id="templateSelect">
                                <option value="">Select Template</option>
                                <option value="default">Default Ballot</option>
                                <option value="modern">Modern Design</option>
                                <option value="traditional">Traditional</option>
                                <option value="compact">Compact</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary w-100" id="applyTemplate">
                                Apply Template
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Ballot Canvas</h6>
                        </div>
                        <div class="card-body">
                            <div id="ballotCanvas" class="ballot-canvas">
                                <!-- Ballot design will be rendered here -->
                                <div class="ballot-paper">
                                    <div class="ballot-header text-center mb-4">
                                        <h3><?= htmlspecialchars($election['name']) ?></h3>
                                        <p class="text-muted">Official Ballot</p>
                                    </div>
                                    
                                    <?php
                                    // Get positions and candidates
                                    $positions = $conn->query("
                                        SELECT p.* FROM positions p 
                                        WHERE p.electionID = $election_id
                                        ORDER BY p.order_num
                                    ");
                                    
                                    while ($position = $positions->fetch_assoc()):
                                        $candidates = $conn->query("
                                            SELECT * FROM candidates 
                                            WHERE positionID = {$position['positionID']}
                                            ORDER BY last_name, first_name
                                        ");
                                    ?>
                                    <div class="position-section mb-4">
                                        <h5><?= htmlspecialchars($position['name']) ?></h5>
                                        <p class="text-muted"><?= htmlspecialchars($position['description']) ?></p>
                                        <p><small>Vote for <?= $position['max_votes'] ?> candidate(s)</small></p>
                                        
                                        <div class="candidates-list">
                                            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                            <div class="candidate-item">
                                                <input type="checkbox" id="candidate_<?= $candidate['candidateID'] ?>" 
                                                       name="position_<?= $position['positionID'] ?>[]" 
                                                       value="<?= $candidate['candidateID'] ?>">
                                                <label for="candidate_<?= $candidate['candidateID'] ?>">
                                                    <?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?>
                                                </label>
                                            </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    
                                    <div class="ballot-footer text-center mt-4">
                                        <p class="text-muted">Thank you for voting!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ballot-canvas {
    background-color: #f8f9fa;
    padding: 2rem;
}

.ballot-paper {
    background-color: white;
    padding: 2rem;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.position-section {
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
}

.candidates-list {
    margin-top: 1rem;
}

.candidate-item {
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    border-radius: 4px;
    background-color: #f8f9fa;
}

.candidate-item:hover {
    background-color: #e9ecef;
}
</style>

<script>
$(document).ready(function() {
    // Initialize the ballot designer
    const canvas = document.getElementById('ballotCanvas');
    
    // Template selection
    $('#applyTemplate').click(function() {
        const template = $('#templateSelect').val();
        if (template) {
            alert('Applying ' + template + ' template...');
            // Here you would implement template application logic
        }
    });
    
    // Design element buttons
    $('.design-element').click(function() {
        const type = $(this).data('type');
        alert('Adding ' + type + ' element to ballot...');
        // Implement element addition logic
    });
    
    // Preview button
    $('#previewBallot').click(function() {
        window.location.href = '?page=ballots&action=preview&election=<?= $election_id ?>';
    });
    
    // Save design
    $('#saveDesign').click(function() {
        // Implement save logic
        alert('Ballot design saved successfully!');
    });
});
</script>