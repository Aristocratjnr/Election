<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="?page=election_config&action=general&election=<?= $election_id ?>">
                        <i class="bi bi-sliders me-1"></i> General
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=election_config&action=voting&election=<?= $election_id ?>">
                        <i class="bi bi-check2-square me-1"></i> Voting Rules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=election_config&action=eligibility&election=<?= $election_id ?>">
                        <i class="bi bi-person-check me-1"></i> Eligibility
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=election_config&action=security&election=<?= $election_id ?>">
                        <i class="bi bi-shield-lock me-1"></i> Security
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <h4 class="mb-4">General Settings: <?= htmlspecialchars($election['name']) ?></h4>
            
            <form action="controllers/election_config_handler.php" method="POST">
                <input type="hidden" name="action" value="general">
                <input type="hidden" name="election_id" value="<?= $election_id ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Election Name</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= htmlspecialchars($election['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Election Type</label>
                        <select class="form-select" name="type" required>
                            <option value="General" <?= ($election['type'] == 'General') ? 'selected' : '' ?>>General Election</option>
                            <option value="Primary" <?= ($election['type'] == 'Primary') ? 'selected' : '' ?>>Primary Election</option>
                            <option value="Referendum" <?= ($election['type'] == 'Referendum') ? 'selected' : '' ?>>Referendum</option>
                            <option value="By-election" <?= ($election['type'] == 'By-election') ? 'selected' : '' ?>>By-election</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" name="start_date" 
                               value="<?= date('Y-m-d\TH:i', strtotime($election['start_date'])) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" name="end_date" 
                               value="<?= date('Y-m-d\TH:i', strtotime($election['end_date'])) ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($election['description']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="resultsVisibility" name="results_visible"
                               <?= ($election['results_visible'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="resultsVisibility">
                            Make results publicly visible after election ends
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allowWriteIns" name="allow_write_ins"
                               <?= ($election['allow_write_ins'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="allowWriteIns">
                            Allow write-in candidates
                        </label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-secondary me-2">Reset Changes</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>