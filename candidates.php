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
        $positionID = $_POST['positionID'];
        $manifesto = $_POST['manifesto'];
        $status = $_POST['status'];
        $photo = '';

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/candidates/' . $new_filename;
                
                if (!is_dir('uploads/candidates')) {
                    mkdir('uploads/candidates', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo = $new_filename;
                }
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO candidates (studentID, positionID, manifesto, status, photo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $studentID, $positionID, $manifesto, $status, $photo);
        
        if ($stmt->execute()) {
            $success = "Candidate added successfully!";
        } else {
            $error = "Error adding candidate: " . $conn->error;
        }
    } elseif (isset($_POST['update_candidate'])) {
        $candidateID = $_POST['candidateID'];
        $studentID = $_POST['studentID'];
        $positionID = $_POST['positionID'];
        $manifesto = $_POST['manifesto'];
        $status = $_POST['status'];
        $photo = $_POST['current_photo'];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/candidates/' . $new_filename;
                
                if (!is_dir('uploads/candidates')) {
                    mkdir('uploads/candidates', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    // Delete old photo if exists
                    if ($photo && file_exists('uploads/candidates/' . $photo)) {
                        unlink('uploads/candidates/' . $photo);
                    }
                    $photo = $new_filename;
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE candidates SET studentID = ?, positionID = ?, manifesto = ?, status = ?, photo = ? WHERE candidateID = ?");
        $stmt->bind_param("iisssi", $studentID, $positionID, $manifesto, $status, $photo, $candidateID);
        
        if ($stmt->execute()) {
            $success = "Candidate updated successfully!";
        } else {
            $error = "Error updating candidate: " . $conn->error;
        }
    } elseif (isset($_POST['delete_candidate'])) {
        $candidateID = $_POST['candidateID'];
        
        // Get photo filename before deletion
        $stmt = $conn->prepare("SELECT photo FROM candidates WHERE candidateID = ?");
        $stmt->bind_param("i", $candidateID);
        $stmt->execute();
        $result = $stmt->get_result();
        $candidate = $result->fetch_assoc();
        
        // Delete photo file if exists
        if ($candidate['photo'] && file_exists('uploads/candidates/' . $candidate['photo'])) {
            unlink('uploads/candidates/' . $candidate['photo']);
        }
        
        $stmt = $conn->prepare("DELETE FROM candidates WHERE candidateID = ?");
        $stmt->bind_param("i", $candidateID);
        
        if ($stmt->execute()) {
            $success = "Candidate deleted successfully!";
        } else {
            $error = "Error deleting candidate: " . $conn->error;
        }
    }
}

// Get all students for dropdown
$students = $conn->query("SELECT studentID, name FROM students ORDER BY name ASC");

// Get all positions for dropdown
$positions = $conn->query("SELECT positionID, title FROM positions ORDER BY title ASC");

// Get all candidates with student and position details
$candidates = $conn->query("
    SELECT c.*, s.name as studentName, s.profilePicture, p.title as positionTitle
    FROM candidates c
    JOIN students s ON c.studentID = s.studentID
    LEFT JOIN positions p ON c.positionID = p.positionID
    ORDER BY s.name ASC
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
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
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
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(45deg, #4e73df, #224abe);
            color: white;
            border-radius: 1rem 1rem 0 0;
            padding: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 2rem;
        }

        .photo-upload-container {
            position: relative;
            width: 100%;
            min-height: 200px;
            border: 2px dashed #e0e4ec;
            border-radius: 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #f8f9fc;
            transition: all 0.3s ease;
        }

        .photo-upload-container:hover {
            border-color: #4e73df;
            background-color: #f0f2ff;
        }

        .photo-upload-container.dragover {
            border-color: #4e73df;
            background-color: #f0f2ff;
        }

        .photo-preview-container {
            width: 100%;
            text-align: center;
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .upload-icon {
            font-size: 2.5rem;
            color: #4e73df;
            margin-bottom: 1rem;
        }

        .upload-text {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            font-size: 0.875rem;
            color: #adb5bd;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #e0e4ec;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .modal-footer {
            border-top: 1px solid #e0e4ec;
            padding: 1.5rem;
        }

        .btn-modal {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }

        .page-header {
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            width: 100%;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            width: 100%;
        }

        .page-subtitle {
            font-size: 1rem;
            color: #718096;
            margin-bottom: 0;
            width: 100%;
        }

        .header-content {
            width: 100%;
            max-width: 800px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 320px;
        }

        @media (max-width: 768px) {
            .page-header .d-flex {
                flex-direction: column;
                gap: 1rem;
            }
            
            .header-actions {
                width: 100%;
                min-width: auto;
                justify-content: space-between;
            }

            .badge-count {
                min-width: auto;
            }

            .btn-primary {
                min-width: auto;
            }
        }

        .badge-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.5rem;
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            min-width: 140px;
        }

        .badge-count i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .btn-primary i {
            margin-right: 0.5rem;
        }

        .position-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
            background-color: rgba(78, 115, 223, 0.1);
            color: #4e73df;
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

        .candidate-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .photo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-top: 10px;
        }

        .table td {
            vertical-align: middle;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <?php include 'includes/header.php'; ?>

            <div class="w-75 mx-auto">
                <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
                    <div class="main-content">
                        <main class="col-md-7 ms-sm-auto col-lg-10 px-6 py-5">
                            <!-- Page Header -->
                            <div class="page-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="header-content">
                                        <h1 class="page-title">
                                            <i class="fas fa-users me-2"></i>
                                            Manage Candidates
                                        </h1>
                                        <p class="page-subtitle">Create and manage election candidates</p>
                                    </div>
                                    <div class="header-actions">
                                        <span class="badge-count">
                                            <i class="fas fa-user-check"></i>
                                            <?php echo $candidates->num_rows; ?> Candidates
                                        </span>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                                            <i class="fas fa-plus"></i>
                                            New Candidate
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

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <span><?php echo $error; ?></span>
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
                                                        <th>Status</th>
                                                        <th width="120">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if ($candidate['photo']): ?>
                                                                    <img src="uploads/candidates/<?= htmlspecialchars($candidate['photo'] ?? '') ?>" 
                                                                         class="candidate-img" 
                                                                         alt="<?= htmlspecialchars($candidate['studentName'] ?? '') ?>">
                                                                <?php else: ?>
                                                                    <div class="candidate-img bg-light d-flex align-items-center justify-content-center">
                                                                        <i class="fas fa-user text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div>
                                                                        <h6 class="mb-0"><?= htmlspecialchars($candidate['studentName'] ?? '') ?></h6>
                                                                        <small class="text-muted">ID: <?= $candidate['studentID'] ?? '' ?></small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="position-badge">
                                                                    <?= htmlspecialchars($candidate['positionTitle'] ?? '') ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="status-badge bg-<?php 
                                                                    echo $candidate['status'] === 'Approved' ? 'success' : 
                                                                        ($candidate['status'] === 'Pending' ? 'warning' : 'danger'); 
                                                                ?>">
                                                                    <?= htmlspecialchars($candidate['status'] ?? '') ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#editCandidateModal"
                                                                            data-id="<?= $candidate['candidateID'] ?? '' ?>"
                                                                            data-studentid="<?= $candidate['studentID'] ?? '' ?>"
                                                                            data-positionid="<?= $candidate['positionID'] ?? '' ?>"
                                                                            data-manifesto="<?= htmlspecialchars($candidate['manifesto'] ?? '') ?>"
                                                                            data-status="<?= htmlspecialchars($candidate['status'] ?? '') ?>"
                                                                            data-photo="<?= htmlspecialchars($candidate['photo'] ?? '') ?>">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="candidateID" value="<?= $candidate['candidateID'] ?? '' ?>">
                                                                        <button type="submit" name="delete_candidate" 
                                                                                class="btn btn-sm btn-outline-danger" 
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
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Candidate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="studentID" class="form-label">Student</label>
                            <select class="form-select" id="studentID" name="studentID" required>
                                <option value="">Select Student</option>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?= $student['studentID'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="positionID" class="form-label">Position</label>
                            <select class="form-select" id="positionID" name="positionID" required>
                                <option value="">Select Position</option>
                                <?php while ($position = $positions->fetch_assoc()): ?>
                                    <option value="<?= $position['positionID'] ?>"><?= htmlspecialchars($position['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="manifesto" class="form-label">Manifesto</label>
                            <textarea class="form-control" id="manifesto" name="manifesto" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_candidate" class="btn btn-primary">Add Candidate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div class="modal fade" id="editCandidateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Candidate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="candidateID" id="edit_candidateID">
                    <input type="hidden" name="current_photo" id="edit_current_photo">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_studentID" class="form-label">Student</label>
                            <select class="form-select" id="edit_studentID" name="studentID" required>
                                <option value="">Select Student</option>
                                <?php 
                                $students->data_seek(0);
                                while ($student = $students->fetch_assoc()): 
                                ?>
                                    <option value="<?= $student['studentID'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_positionID" class="form-label">Position</label>
                            <select class="form-select" id="edit_positionID" name="positionID" required>
                                <option value="">Select Position</option>
                                <?php 
                                $positions->data_seek(0);
                                while ($position = $positions->fetch_assoc()): 
                                ?>
                                    <option value="<?= $position['positionID'] ?>"><?= htmlspecialchars($position['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_manifesto" class="form-label">Manifesto</label>
                            <textarea class="form-control" id="edit_manifesto" name="manifesto" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                            <div id="current_photo_preview" class="mt-2"></div>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_candidate" class="btn btn-primary">Update Candidate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_candidateID').value = this.dataset.id;
                document.getElementById('edit_studentID').value = this.dataset.studentid;
                document.getElementById('edit_positionID').value = this.dataset.positionid;
                document.getElementById('edit_manifesto').value = this.dataset.manifesto;
                document.getElementById('edit_status').value = this.dataset.status;
                document.getElementById('edit_current_photo').value = this.dataset.photo;
                
                // Show current photo preview
                const preview = document.getElementById('current_photo_preview');
                if (this.dataset.photo) {
                    preview.innerHTML = `<img src="uploads/candidates/${this.dataset.photo}" class="img-thumbnail" style="max-height: 100px;">`;
                } else {
                    preview.innerHTML = '';
                }
            });
        });

        // Enhanced photo upload functionality
        document.addEventListener('DOMContentLoaded', function() {
            const photoUploadContainer = document.getElementById('photoUploadContainer');
            const photoInput = document.getElementById('photo');
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');

            // Click to upload
            photoUploadContainer.addEventListener('click', function() {
                photoInput.click();
            });

            // Drag and drop functionality
            photoUploadContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            photoUploadContainer.addEventListener('dragleave', function() {
                this.classList.remove('dragover');
            });

            photoUploadContainer.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    photoInput.files = e.dataTransfer.files;
                    previewImage(photoInput, 'addPhotoPreview');
                }
            });
        });

        // Enhanced image preview function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById('uploadPlaceholder');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Check file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.classList.add('d-none');
                placeholder.style.display = 'block';
            }
        }

        // Reset forms when modals are closed
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function() {
                this.querySelector('form').reset();
                document.querySelectorAll('.photo-preview').forEach(preview => {
                    preview.src = '#';
                    preview.classList.add('d-none');
                });
            });
        });
    </script>
</body>
</html>