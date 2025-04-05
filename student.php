<?php
require __DIR__ . '/configs/dbconnection.php';
session_start();

// Check admin status (from students table)
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?reason=admin_required");
    exit();
}

// Handle election creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_election'])) {
    $electionName = $conn->real_escape_string($_POST['election_name']);
    $startDate = $conn->real_escape_string($_POST['start_date']);
    $endDate = $conn->real_escape_string($_POST['end_date']);
    
    $stmt = $conn->prepare("INSERT INTO elections (name, start_date, end_date, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sssi', $electionName, $startDate, $endDate, $_SESSION['login_id']);
    
    if ($stmt->execute()) {
        $success = "Election created successfully!";
    } else {
        $error = "Error creating election: " . $conn->error;
    }
}

// Get existing elections
$elections = $conn->query("
    SELECT e.*, s.name as creator 
    FROM elections e
    JOIN students s ON e.created_by = s.studentID
    ORDER BY e.start_date DESC
")->fetch_all(MYSQLI_ASSOC);

function getElectionStatus($start, $end) {
    $now = time();
    $start = strtotime($start);
    $end = strtotime($end);
    
    if ($now < $start) return 'Upcoming';
    if ($now > $end) return 'Completed';
    return 'Active';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Creation | Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 280px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: #1a237e;
            transition: all 0.3s;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }
        .election-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .election-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content w-100">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-link d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="mb-0">Election Creation Portal</h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3 small text-muted"><?= $_SESSION['name'] ?? 'Admin' ?></span>
                        <img src="assets/img/default-avatar.png" width="35" height="35" class="rounded-circle">
                    </div>
                </div>
            </nav>
            
            <!-- Page Content -->
            <div class="container-fluid p-4">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Election Creation Form -->
                    <div class="col-lg-5 mb-4">
                        <div class="card election-card border-0">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> New Election</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Election Name</label>
                                        <input type="text" name="election_name" class="form-control" required>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Start Date</label>
                                            <input type="datetime-local" name="start_date" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">End Date</label>
                                            <input type="datetime-local" name="end_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="create_election" class="btn btn-primary w-100">
                                        <i class="bi bi-save me-2"></i> Create Election
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Elections List -->
                    <div class="col-lg-7">
                        <div class="card election-card border-0">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Current Elections</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($elections)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">No elections created yet</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Election</th>
                                                    <th>Period</th>
                                                    <th>Status</th>
                                                    <th>Created By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($elections as $election): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= htmlspecialchars($election['name']) ?></strong>
                                                        </td>
                                                        <td>
                                                            <?= date('M j, Y', strtotime($election['start_date'])) ?> - 
                                                            <?= date('M j, Y', strtotime($election['end_date'])) ?>
                                                        </td>
                                                        <td>
                                                            <?php $status = getElectionStatus($election['start_date'], $election['end_date']); ?>
                                                            <span class="status-badge bg-<?= 
                                                                $status === 'Active' ? 'success' : 
                                                                ($status === 'Upcoming' ? 'warning' : 'secondary') 
                                                            ?>">
                                                                <?= $status ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($election['creator']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.querySelector('[data-bs-toggle="collapse"]').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('active');
        });
    </script>
</body>
</html>