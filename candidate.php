<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .sidebar {
            width: 120px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .main-content {
            margin-left: 200px;
            width: calc(100% - 280px);
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s ease;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .table th {
            background-color: #f8f9fc;
            color: var(--dark-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-top: none;
        }

        .table td {
            vertical-align: middle;
            border-top: none;
            background-color: white;
        }

        .table tr:first-child td {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.7rem;
        }

        .table tr:last-child td {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }

        .candidate-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            padding: 2px;
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 1rem;
            font-weight: 500;
        }

        .action-btn-group .btn {
            margin-right: 0.5rem;
            transition: all 0.2s ease;
        }


        .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 0.75rem 0.75rem 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .alert {
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
        }

        .animate-bounce {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <?php include 'includes/header.php'; ?><br><br>
            <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
            <div class="main-content">
                <main class="col-md-7 ms-sm-auto col-lg-10 px-4 py-5 ">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">
                                <i class="bi bi-people-fill text-primary me-2"></i>
                                Manage Candidates
                            </h1>
                            <p class="mb-0 text-muted">Create and manage election candidates</p>
                        </div>
                        <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                            <i class="bi bi-plus-lg me-2"></i>New Candidate
                        </button>
                    </div>

                    <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Candidates Table -->
                    <div class="card hover-effect">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-table me-2"></i>
                                Current Candidates
                            </h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <?php echo $candidates->num_rows; ?> Candidates
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="100">Photo</th>
                                            <th>Candidate</th>
                                            <th>Position</th>
                                            <th width="150">Status</th>
                                            <th width="180">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                        <tr class="hover-shade animate__animated animate__fadeIn">
                                            <td>
                                                <?php if ($candidate['profilePicture']): ?>
                                                <img src="assets/img/profile/students/<?php echo $candidate['profilePicture']; ?>" 
                                                     class="candidate-img" 
                                                     alt="<?php echo htmlspecialchars($candidate['studentName']); ?>">
                                                <?php else: ?>
                                                <div class="candidate-img bg-light d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-person text-muted fs-4"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($candidate['studentName']); ?></h6>
                                                <small class="text-muted">ID: <?php echo $candidate['studentID']; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                                    <?php echo htmlspecialchars($candidate['position']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge bg-<?php 
                                                    echo $candidate['status'] === 'Approved' ? 'success' : 
                                                        ($candidate['status'] === 'Pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo $candidate['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-btn-group d-flex">
                                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCandidateModal"
                                                            data-id="<?php echo $candidate['candidateID']; ?>"
                                                            data-studentid="<?php echo $candidate['studentID']; ?>"
                                                            data-position="<?php echo htmlspecialchars($candidate['position']); ?>"
                                                            data-manifesto="<?php echo htmlspecialchars($candidate['manifesto']); ?>"
                                                            data-status="<?php echo $candidate['status']; ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" class="ms-2">
                                                        <input type="hidden" name="candidateID" value="<?php echo $candidate['candidateID']; ?>">
                                                        <button type="submit" name="delete_candidate" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this candidate?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-plus me-2"></i>
                            New Candidate
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="studentID" class="form-label">Student</label>
                                <select class="form-select" id="studentID" name="studentID" required>
                                    <option value="">Select Student</option>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['studentID']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                            </div>
                            <div class="col-12">
                                <label for="manifesto" class="form-label">Manifesto</label>
                                <textarea class="form-control" id="manifesto" name="manifesto" rows="3" placeholder="Enter candidate's manifesto..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_candidate" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i>Save Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div class="modal fade" id="editCandidateModal" tabindex="-1" aria-labelledby="editCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-gear me-2"></i>
                            Edit Candidate
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_candidateID" name="candidateID">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_studentID" class="form-label">Student</label>
                                <select class="form-select" id="edit_studentID" name="studentID" required>
                                    <option value="">Select Student</option>
                                    <?php 
                                    $students->data_seek(0);
                                    while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['studentID']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="edit_position" name="position" required>
                            </div>
                            <div class="col-12">
                                <label for="edit_manifesto" class="form-label">Manifesto</label>
                                <textarea class="form-control" id="edit_manifesto" name="manifesto" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_candidate" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i>Update Candidate
                        </button>
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

        // Initialize form validation
        (function() {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
   
</body>
</html>