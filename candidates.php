<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); 
    exit();
}

require 'configs/dbconnection.php';

$topDept = 'None';
if (!empty($departments)) {
    reset($departments);
    $topDept = key($departments);
}

$electionID = $_GET['election'] ?? null;

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

// Get positions for dropdown (filtered by election if specified)
$positionsQuery = "SELECT positionID, title FROM positions";
if ($electionID) {
    $positionsQuery .= " WHERE electionID = $electionID";
}
$positionsQuery .= " ORDER BY title ASC";
$positions = $conn->query($positionsQuery);

// Base query for candidates
$candidatesQuery = "
    SELECT DISTINCT c.candidateID, c.studentID, c.positionID, c.manifesto, c.status, c.photo,
           s.name as studentName, s.profilePicture, p.title as positionTitle 
    FROM candidates c
    JOIN students s ON c.studentID = s.studentID
    LEFT JOIN positions p ON c.positionID = p.positionID
";

// Add filter by election if provided
if ($electionID) {
    $candidatesQuery .= " WHERE p.electionID = $electionID";
}

// Add sorting
$candidatesQuery .= " ORDER BY s.name ASC";

// Get candidates
$candidates = $conn->query($candidatesQuery);

// Get election details if ID is provided
$electionDetails = null;
if ($electionID) {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE electionID = ?");
    $stmt->bind_param("i", $electionID);
    $stmt->execute();
    $electionDetails = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - EMS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-hover: #0b5ed7;
            --secondary-color: #f8f9fc;
            --success-color: #198754;
            --success-hover: #157347;
            --warning-color: #ffc107;
            --warning-hover: #ffca2c;
            --danger-color: #dc3545;
            --danger-hover: #bb2d3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        body {
            background-color: #f7f9fc;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            color: #444;
        }

        .sidebar {
            width: 100px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #0b5ed7 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .main-content {
            margin-left: 120px;
            width: calc(100% - 280px);
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
        }

       
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
        }

        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: #333;
        }

        .card-header i {
            color: var(--primary-color);
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 0 0 0.75rem 0.75rem;
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transition: transform 0.3s ease;
        }

        .candidate-img:hover {
            transform: scale(1.1);
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

        

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: var(--transition);
        }

       
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            transition: var(--transition);
        }

       

        .btn-outline-danger {
            color: var(--danger-color);
            border-color: var(--danger-color);
            transition: var(--transition);
        }

        .btn-outline-danger:hover {
            background-color: var(--danger-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }

        .modal-content {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
            color: white;
            border-radius: 1rem 1rem 0 0;
            padding: 1.5rem;
            border-bottom: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .modal-header .btn-close:hover {
            opacity: 1;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .modal-title i {
            margin-right: 0.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .photo-upload-container {
            position: relative;
            width: 100%;
            min-height: 200px;
            border: 2px dashed #e0e4ec;
            border-radius: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #f8f9fc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-upload-container:hover {
            border-color: #0d6efd;
            background-color: #f0f7ff;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .photo-preview:hover {
            transform: scale(1.05);
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .form-control, .form-select {
            border-radius: 0.75rem;
            border: 1px solid #e0e4ec;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .modal-footer {
            border-top: 1px solid #e0e4ec;
            padding: 1.5rem;
        }

        .btn-modal {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.75rem;
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
            display: flex;
            align-items: center;
        }

        .page-title i {
            color: var(--primary-color);
            margin-right: 0.75rem;
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
            border-radius: 0.75rem;
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            min-width: 140px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        

        .badge-count i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
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
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
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

        /* Animation for status badges */
        .status-badge {
            transition: all 0.3s ease;
        }
        
        .status-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Improved empty state */
        .empty-state {
            background: linear-gradient(145deg, #ffffff, #f7f9fc);
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border-radius: 16px;
            padding: 4rem 2rem;
            transition: all 0.3s ease;
        }
        
        .empty-state:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        
        /* Animation for alerts */
        .alert {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
                                            <i class="bi bi-people me-2"></i>
                                            Manage Candidates
                                        </h1>
                                        <p class="page-subtitle">Create and manage election candidates</p>
                                    </div>
                                    <div class="header-actions">
                                        <span class="badge-count">
                                            <i class="bi bi-person-check"></i>
                                            <?php echo $candidates->num_rows; ?> Candidates
                                        </span>
                                        <button class="btn badge-count bg-primary text-white d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                                            <i class="bi bi-plus-lg me-2"></i>
                                            Add Candidate
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if ($electionDetails): ?>
                                <div class="election-time-info mt-3 p-3 bg-light rounded-3 border shadow-sm animate__animated animate__fadeIn">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <div class="me-4 mb-2 mb-md-0">
                                            <h6 class="fw-bold mb-2 d-flex align-items-center">
                                                <i class="bi bi-calendar-event-fill me-2 text-primary"></i> Election Period
                                            </h6>
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-calendar-plus-fill text-success me-2"></i>
                                                <span>Start: <?php echo date('M j, Y - h:i A', strtotime($electionDetails['startDate'])); ?></span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-x-fill text-danger me-2"></i>
                                                <span>End: <?php echo date('M j, Y - h:i A', strtotime($electionDetails['endDate'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="me-4 mb-2 mb-md-0">
                                            <h6 class="fw-bold mb-2 d-flex align-items-center">
                                                <i class="bi bi-info-circle-fill me-2 text-primary"></i> Status
                                            </h6>
                                            <div class="status-badge bg-<?php 
                                                echo $electionDetails['status'] === 'Ongoing' ? 'success' : 
                                                    ($electionDetails['status'] === 'Scheduled' ? 'warning' : 'danger'); 
                                                ?> text-white px-3 py-2 rounded-pill">
                                                <?php if($electionDetails['status'] === 'Ongoing'): ?>
                                                    <i class="bi bi-play-circle-fill me-1"></i>
                                                <?php elseif($electionDetails['status'] === 'Scheduled'): ?>
                                                    <i class="bi bi-clock-fill me-1"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($electionDetails['status'] ?? '') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Election Selector -->
                            <div class="card mb-4 shadow-sm animate__animated animate__fadeIn">
                                <div class="card-body p-3">
                                    <form method="GET" class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <label class="form-label mb-2 fw-bold">
                                                <i class="bi bi-filter-circle-fill me-2 text-primary"></i>
                                                Select Election
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <select class="form-select border-start-0" name="election">
                                                    <option value="">-- All Elections --</option>
                                                    <?php 
                                                    $electionsQuery = $conn->query("SELECT electionID, name, startDate FROM elections ORDER BY startDate DESC");
                                                    while ($election = $electionsQuery->fetch_assoc()): 
                                                    ?>
                                                    <option value="<?= $election['electionID'] ?>" <?= ($electionID == $election['electionID']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($election['name']) ?> 
                                                        (<?= date('M j, Y', strtotime($election['startDate'])) ?>)
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <button type="submit" class="btn btn-primary d-flex align-items-center">
                                                    <i class="bi bi-funnel-fill me-2"></i>
                                                    Filter
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <span><?php echo $success; ?></span>
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        <span><?php echo $error; ?></span>
                                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Candidates Table -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list-ul me-2"></i>
                                        Candidate List
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if ($candidates->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;">#</th>
                                                        <th style="width: 60px;">Photo</th>
                                                        <th>Student</th>
                                                        <th>Position</th>
                                                        <th>Status</th>
                                                        <th style="width: 150px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $count = 1; ?>
                                                    <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?= $count++ ?></td>
                                                            <td>
                                                                <?php if ($candidate['photo'] && file_exists('uploads/candidates/' . $candidate['photo'])): ?>
                                                                    <img src="uploads/candidates/<?= $candidate['photo'] ?>" alt="Candidate Photo" class="candidate-img">
                                                                <?php elseif ($candidate['profilePicture'] && file_exists('uploads/students/' . $candidate['profilePicture'])): ?>
                                                                    <img src="uploads/students/<?= $candidate['profilePicture'] ?>" alt="Candidate Photo" class="candidate-img">
                                                                <?php else: ?>
                                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 50px; height: 50px;">
                                                                        <i class="bi bi-person text-primary"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <span class="student-name"><?= htmlspecialchars($candidate['studentName']) ?></span>
                                                                    <span class="student-id text-muted">ID: <?= $candidate['studentID'] ?></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="position-badge"><?= htmlspecialchars($candidate['positionTitle']) ?></span>
                                                            </td>
                                                            <td>
                                                                <?php if($candidate['status'] === 'Approved'): ?>
                                                                    <span class="status-badge bg-success-light text-success">
                                                                        <i class="bi bi-check-circle me-1"></i>Approved
                                                                    </span>
                                                                <?php elseif($candidate['status'] === 'Pending'): ?>
                                                                    <span class="status-badge bg-warning-light text-warning">
                                                                        <i class="bi bi-clock-history me-1"></i>Pending
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="status-badge bg-danger-light text-danger">
                                                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-outline-primary action-btn edit-btn" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#editCandidateModal"
                                                                            data-id="<?= $candidate['candidateID'] ?>"
                                                                            data-studentid="<?= $candidate['studentID'] ?>"
                                                                            data-positionid="<?= $candidate['positionID'] ?>"
                                                                            data-manifesto="<?= htmlspecialchars($candidate['manifesto']) ?>"
                                                                            data-status="<?= $candidate['status'] ?>"
                                                                            data-photo="<?= $candidate['photo'] ?>">
                                                                        <i class="bi bi-pencil-square"></i>
                                                                    </button>
                                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                                                        <input type="hidden" name="candidateID" value="<?= $candidate['candidateID'] ?>">
                                                                        <button type="submit" name="delete_candidate" class="btn btn-sm btn-outline-danger action-btn">
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
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="bi bi-person-plus"></i>
                                            </div>
                                            <h4 class="empty-state-title">No Candidates Found</h4>
                                            <p class="empty-state-text">
                                                There are no candidates to display. Click the button below to add your first candidate.
                                            </p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                                                <i class="bi bi-person-plus me-2"></i> Add New Candidate
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Add New Candidate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="studentID" class="form-label"><i class="bi bi-person-badge-fill me-1 text-primary"></i>Student</label>
                            <select class="form-select rounded-pill" id="studentID" name="studentID" required>
                                <option value="">Select Student</option>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?= $student['studentID'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="positionID" class="form-label"><i class="bi bi-award-fill me-1 text-primary"></i>Position</label>
                            <select class="form-select rounded-pill" id="positionID" name="positionID" required>
                                <option value="">Select Position</option>
                                <?php while ($position = $positions->fetch_assoc()): ?>
                                    <option value="<?= $position['positionID'] ?>"><?= htmlspecialchars($position['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="manifesto" class="form-label"><i class="bi bi-file-earmark-text-fill me-1 text-primary"></i>Manifesto</label>
                            <textarea class="form-control rounded-3" id="manifesto" name="manifesto" rows="3" placeholder="Candidate's agenda and promises"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label"><i class="bi bi-camera-fill me-1 text-primary"></i>Photo</label>
                            <input type="file" class="form-control rounded-pill" id="photo" name="photo" accept="image/*">
                            <div id="photoPreview" class="mt-2 text-center d-none">
                                <img src="#" alt="Preview" class="photo-preview">
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Recommended size: 400x400px, max file size: 2MB
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label"><i class="bi bi-shield-fill-check me-1 text-primary"></i>Status</label>
                            <select class="form-select rounded-pill" id="status" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light d-flex align-items-center justify-content-center" style="min-width: 120px; border-radius: 8px;" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" name="add_candidate" class="btn btn-primary d-flex align-items-center justify-content-center" style="min-width: 150px; border-radius: 8px;">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            <span>Add Candidate</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div class="modal fade" id="editCandidateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Candidate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="candidateID" id="edit_candidateID">
                    <input type="hidden" name="current_photo" id="edit_current_photo">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_studentID" class="form-label"><i class="bi bi-person-badge-fill me-1 text-primary"></i>Student</label>
                            <select class="form-select rounded-pill" id="edit_studentID" name="studentID" required>
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
                            <label for="edit_positionID" class="form-label"><i class="bi bi-award-fill me-1 text-primary"></i>Position</label>
                            <select class="form-select rounded-pill" id="edit_positionID" name="positionID" required>
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
                            <label for="edit_manifesto" class="form-label"><i class="bi bi-file-earmark-text-fill me-1 text-primary"></i>Manifesto</label>
                            <textarea class="form-control rounded-3" id="edit_manifesto" name="manifesto" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo" class="form-label"><i class="bi bi-camera-fill me-1 text-primary"></i>Photo</label>
                            <input type="file" class="form-control rounded-pill" id="edit_photo" name="photo" accept="image/*">
                            <div id="current_photo_preview" class="mt-2 text-center"></div>
                            <div class="form-text">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Leave empty to keep current photo
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label"><i class="bi bi-shield-fill-check me-1 text-primary"></i>Status</label>
                            <select class="form-select rounded-pill" id="edit_status" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light d-flex align-items-center justify-content-center" style="min-width: 120px; border-radius: 8px;" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" name="update_candidate" class="btn btn-primary d-flex align-items-center justify-content-center" style="min-width: 150px; border-radius: 8px;">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            <span>Update Candidate</span>
                        </button>
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
                    preview.innerHTML = `<img src="uploads/candidates/${this.dataset.photo}" class="photo-preview shadow-sm" alt="Current Photo">`;
                } else {
                    preview.innerHTML = `<div class="text-center text-muted"><i class="bi bi-image fs-3"></i><p>No current photo</p></div>`;
                }
            });
        });

        // Handle photo upload preview for Add form
        document.getElementById('photo')?.addEventListener('change', function() {
            previewImage(this, 'photoPreview');
        });

        // Handle photo upload preview for Edit form
        document.getElementById('edit_photo')?.addEventListener('change', function() {
            const preview = document.getElementById('current_photo_preview');
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Check file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="photo-preview shadow-sm" alt="New Photo">`;
                }
                reader.readAsDataURL(file);
            }
        });

        // Image preview function
        function previewImage(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            
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
                    previewContainer.innerHTML = `<img src="${e.target.result}" class="photo-preview shadow-sm" alt="Preview">`;
                    previewContainer.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '';
                previewContainer.classList.add('d-none');
            }
        }

        // Show success alerts for 3 seconds then fade out
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.classList.remove('animate__fadeIn');
                successAlert.classList.add('animate__fadeOut');
                setTimeout(() => {
                    successAlert.remove();
                }, 500);
            }, 3000);
        }

        // Reset forms when modals are closed
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function() {
                this.querySelector('form').reset();
                const photoPreview = document.getElementById('photoPreview');
                if (photoPreview) {
                    photoPreview.innerHTML = '';
                    photoPreview.classList.add('d-none');
                }
            });
        });
    </script>
</body>
</html>