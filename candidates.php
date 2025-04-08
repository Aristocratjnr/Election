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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-hover: #3a5bc7;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --success-hover: #17a673;
            --warning-color: #f6c23e;
            --warning-hover: #dda20a;
            --danger-color: #e74a3b;
            --danger-hover: #be2617;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #4a4a4a;
        }

        .sidebar {
            width: 100px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .main-content {
            margin-left: 120px;
            width: calc(100% - 280px);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
        }

       

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8fafd;
            color: var(--dark-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-top: none;
            padding: 1rem 1.5rem;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            border-top: none;
            background-color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        


        .candidate-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: var(--transition);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-outline-danger {
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-outline-danger:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e4ec;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.2);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem 1.5rem;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .page-header {
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #718096;
            font-size: 0.95rem;
        }

        .badge-count {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }

       

        .position-badge {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .student-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .student-id {
            font-size: 0.8rem;
            color: #718096;
        }

        /* Custom scrollbar */
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

        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(78, 115, 223, 0.3);
            z-index: 100;
            transition: var(--transition);
        }

        

        /* Empty state */
        .empty-state {
            padding: 3rem;
            text-align: center;
            background-color: white;
            border-radius: 12px;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #e0e4ec;
            margin-bottom: 1.5rem;
        }

        .empty-state-title {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: #718096;
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <?php include 'includes/header.php'; ?>
            <div class="card w-75 mx-auto">
            <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
            <div class="main-content">
                <ma class="col-md-7 ms-sm-auto col-lg-10 px-4 py-5 ">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="page-title">
                                    <i class="fas fa-users me-2"></i>
                                    Manage Candidates
                                </h1>
                                <p class="page-subtitle">Create and manage election candidates</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge-count me-3">
                                    <i class="fas fa-user-check me-1"></i>
                                    <?php echo $candidates->num_rows; ?> Candidates
                                </span>
                                <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                                    <i class="fas fa-plus me-2"></i>New Candidate
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <span><?php echo $success; ?></span>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Candidates Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list-ul me-2"></i>
                                Candidate List
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($candidates->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="80">Photo</th>
                                            <th>Candidate</th>
                                            <th>Position</th>
                                            <th width="150">Status</th>
                                            <th width="150">Actions</th>
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
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="student-name"><?php echo htmlspecialchars($candidate['studentName']); ?></div>
                                                <div class="student-id">ID: <?php echo $candidate['studentID']; ?></div>
                                            </td>
                                            <td>
                                                <span class="position-badge">
                                                    <?php echo htmlspecialchars($candidate['position']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge bg-<?php 
                                                    echo $candidate['status'] === 'Approved' ? 'success' : 
                                                        ($candidate['status'] === 'Pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <i class="fas fa-<?php 
                                                        echo $candidate['status'] === 'Approved' ? 'check-circle' : 
                                                            ($candidate['status'] === 'Pending' ? 'clock' : 'times-circle'); 
                                                    ?> me-1"></i>
                                                    <?php echo $candidate['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button class="btn btn-sm btn-outline-primary action-btn edit-btn me-2" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCandidateModal"
                                                            data-id="<?php echo $candidate['candidateID']; ?>"
                                                            data-studentid="<?php echo $candidate['studentID']; ?>"
                                                            data-position="<?php echo htmlspecialchars($candidate['position']); ?>"
                                                            data-manifesto="<?php echo htmlspecialchars($candidate['manifesto']); ?>"
                                                            data-status="<?php echo $candidate['status']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST">
                                                        <input type="hidden" name="candidateID" value="<?php echo $candidate['candidateID']; ?>">
                                                        <button type="submit" name="delete_candidate" 
                                                                class="btn btn-sm btn-outline-danger action-btn" 
                                                                onclick="return confirm('Are you sure you want to delete this candidate?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <h4 class="empty-state-title">No Candidates Found</h4>
                                <p class="empty-state-text">You haven't added any candidates yet. Click the button below to add your first candidate.</p>
                                <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                                    <i class="fas fa-plus me-2"></i>Add Candidate
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        </div>
                        </div>
                    </div>
        
                </main>
            </div>
          
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="#" class="fab" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
        <i class="fas fa-plus"></i>
    </a>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus me-2"></i>
                            Add New Candidate
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="studentID" class="form-label">Student</label>
                                <select class="form-select" id="studentID" name="studentID" required>
                                    <option value="">Select Student</option>
                                    <?php 
                                    $students->data_seek(0);
                                    while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['studentID']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" placeholder="E.g., President" required>
                            </div>
                            <div class="col-12">
                                <label for="manifesto" class="form-label">Manifesto</label>
                                <textarea class="form-control" id="manifesto" name="manifesto" rows="4" placeholder="Enter candidate's manifesto..."></textarea>
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
                            <i class="fas fa-save me-2"></i>Save Candidate
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
                            <i class="fas fa-user-edit me-2"></i>
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
                                <textarea class="form-control" id="edit_manifesto" name="manifesto" rows="4"></textarea>
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
                            <i class="fas fa-save me-2"></i>Update Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        // Enhanced delete confirmation
        document.querySelectorAll('[name="delete_candidate"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    background: '#ffffff',
                    backdrop: `
                        rgba(0,0,0,0.4)
                        url("/images/nyan-cat.gif")
                        left top
                        no-repeat
                    `
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
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