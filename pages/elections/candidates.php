<?php
require_once '../../includes/auth_check.php';
require_once '../../configs/dbconnection.php';

// Fetch all candidates with their positions and elections
$candidates = [];
try {
    $query = "SELECT c.*, p.position_name, e.name as election_name, s.name as student_name 
              FROM candidates c 
              LEFT JOIN positions p ON c.positionID = p.positionID
              LEFT JOIN elections e ON p.electionID = e.electionID
              LEFT JOIN students s ON c.studentID = s.studentID
              ORDER BY e.startDate DESC, p.position_order ASC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
    }
} catch (Exception $e) {
    $error = "Error fetching candidates: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Election System</title>
    <!-- Include your CSS files -->
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Candidates</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                        <i class="bi bi-plus-circle"></i> Add Candidate
                    </button>
                </div>
                
                <!-- Candidates list -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Election</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                            <tr>
                                <td>
                                    <img src="../../assets/img/candidates/<?= htmlspecialchars($candidate['photo']) ?>" 
                                         class="rounded-circle" 
                                         width="40" 
                                         height="40"
                                         alt="Candidate photo">
                                </td>
                                <td><?= htmlspecialchars($candidate['student_name']) ?></td>
                                <td><?= htmlspecialchars($candidate['position_name']) ?></td>
                                <td><?= htmlspecialchars($candidate['election_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $candidate['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($candidate['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-candidate" 
                                            data-id="<?= $candidate['candidateID'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-candidate" 
                                            data-id="<?= $candidate['candidateID'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1">
        <!-- Add your modal content here -->
    </div>

    <!-- Include your JavaScript files -->
</body>
</html>