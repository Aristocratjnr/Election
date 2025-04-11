<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';

$action = $_GET['action'] ?? 'manage';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-people-fill me-2"></i>
                    Voter Management
                </h4>
                <a href="voters.php?action=create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-2"></i> Add Voter
                </a>
            </div>

            <div class="card-body">
                <?php if ($action === 'manage'): ?>
                    <!-- Voters List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM students ORDER BY name ASC";
                                $result = $conn->query($query);
                                while ($voter = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($voter['name']) ?></td>
                                    <td><?= htmlspecialchars($voter['email']) ?></td>
                                    <td><?= htmlspecialchars($voter['department']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            ($voter['status'] === 'Active') ? 'success' : 'danger'
                                        ?>">
                                            <?= $voter['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="voters.php?action=edit&id=<?= $voter['studentID'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger toggle-status" 
                                                data-id="<?= $voter['studentID'] ?>">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Voter Form -->
                    <form method="POST" action="save_voter.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select class="form-select" name="department" required>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Business">Business</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>
                                Save Voter
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>