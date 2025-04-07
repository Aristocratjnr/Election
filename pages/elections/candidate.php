<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require '../../configs/dbconnection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_candidate'])) {
        $studentID = $_POST['studentID'];
        $position = $_POST['position'];
        $manifesto = $_POST['manifesto'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("INSERT INTO candidates (studentID, position, manifesto, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $studentID, $position, $manifesto, $status);
        $stmt->execute();
        $success = "Candidate added successfully!";
    } elseif (isset($_POST['update_candidate'])) {
        $candidateID = $_POST['candidateID'];
        $studentID = $_POST['studentID'];
        $position = $_POST['position'];
        $manifesto = $_POST['manifesto'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE candidates SET studentID = ?, position = ?, manifesto = ?, status = ? WHERE candidateID = ?");
        $stmt->bind_param("isssi", $studentID, $position, $manifesto, $status, $candidateID);
        $stmt->execute();
        $success = "Candidate updated successfully!";
    } elseif (isset($_POST['delete_candidate'])) {
        $candidateID = $_POST['candidateID'];
        
        $stmt = $conn->prepare("DELETE FROM candidates WHERE candidateID = ?");
        $stmt->bind_param("i", $candidateID);
        $stmt->execute();
        $success = "Candidate deleted successfully!";
    }
}

// Get all students for dropdown
$students = $conn->query("SELECT studentID, name FROM students ORDER BY name ASC");

// Get all candidates with student details
$candidates = $conn->query("
    SELECT c.*, s.name as studentName, s.profilePicture 
    FROM candidates c
    JOIN students s ON c.studentID = s.studentID
    ORDER BY c.candidateID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
        }
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
        }
        .card {
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        .table-responsive {
            overflow-x: auto;
        }
        .candidate-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            <div class="main-content"> 
                <main class="col-md-9 ms-sm-auto col-lg-14 px-md-4 py-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Manage Candidates</h1>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                            <i class="bi bi-plus-circle"></i> Add Candidate
                        </button>
                    </div>
                    
                    <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Current Candidates</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Candidate</th>
                                            <th>Position</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if ($candidate['profilePicture']): ?>
                                                <img src="assets/img/profile/students/<?php echo $candidate['profilePicture']; ?>" class="candidate-img" alt="<?php echo htmlspecialchars($candidate['studentName']); ?>">
                                                <?php else: ?>
                                                <div class="candidate-img bg-light d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($candidate['studentName']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $candidate['status'] === 'Approved' ? 'success' : 
                                                        ($candidate['status'] === 'Pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo $candidate['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCandidateModal"
                                                        data-id="<?php echo $candidate['candidateID']; ?>"
                                                        data-studentid="<?php echo $candidate['studentID']; ?>"
                                                        data-position="<?php echo htmlspecialchars($candidate['position']); ?>"
                                                        data-manifesto="<?php echo htmlspecialchars($candidate['manifesto']); ?>"
                                                        data-status="<?php echo $candidate['status']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="candidateID" value="<?php echo $candidate['candidateID']; ?>">
                                                    <button type="submit" name="delete_candidate" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCandidateModalLabel">Add New Candidate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="studentID" class="form-label">Student</label>
                            <select class="form-select" id="studentID" name="studentID" required>
                                <option value="">Select Student</option>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                <option value="<?php echo $student['studentID']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="manifesto" class="form-label">Manifesto</label>
                            <textarea class="form-control" id="manifesto" name="manifesto" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_candidate" class="btn btn-primary">Add Candidate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div class="modal fade" id="editCandidateModal" tabindex="-1" aria-labelledby="editCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCandidateModalLabel">Edit Candidate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_candidateID" name="candidateID">
                        <div class="mb-3">
                            <label for="edit_studentID" class="form-label">Student</label>
                            <select class="form-select" id="edit_studentID" name="studentID" required>
                                <option value="">Select Student</option>
                                <?php 
                                $students->data_seek(0); // Reset pointer to beginning
                                while ($student = $students->fetch_assoc()): ?>
                                <option value="<?php echo $student['studentID']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="edit_position" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_manifesto" class="form-label">Manifesto</label>
                            <textarea class="form-control" id="edit_manifesto" name="manifesto" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_candidate" class="btn btn-primary">Update Candidate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_candidateID').value = this.dataset.id;
                document.getElementById('edit_studentID').value = this.dataset.studentid;
                document.getElementById('edit_position').value = this.dataset.position;
                document.getElementById('edit_manifesto').value = this.dataset.manifesto;
                document.getElementById('edit_status').value = this.dataset.status;
            });
        });
    </script>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>