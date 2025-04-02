<?php
session_start();
require 'configs/dbconnection.php';
require 'configs/session.php';

// Check if user is logged in and has the student role
if (!isset($_SESSION['login_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.html'); // Redirect to login page if not logged in
    exit();
}

$studentID = (int)$_SESSION['login_id'];

// Check if student has already voted in current election
$hasVoted = false;
$currentElection = null;

try {
    // Get current active election
    $stmt = $conn->prepare("SELECT * FROM elections WHERE status = 1 AND start_date <= NOW() AND end_date >= NOW() LIMIT 1");
    $stmt->execute();
    $currentElection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($currentElection) {
        // Check if student has already voted
        $stmt = $conn->prepare("SELECT 1 FROM votes WHERE student_id = ? AND election_id = ?");
        $stmt->bind_param('ii', $studentID, $currentElection['electionID']);
        $stmt->execute();
        $hasVoted = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Election check error: " . $e->getMessage());
    $error = "Error checking election status";
}

// Get student details
$student = [];
try {
    $stmt = $conn->prepare("SELECT * FROM students WHERE studentID = ?");
    $stmt->bind_param('i', $studentID);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    error_log("Student fetch error: " . $e->getMessage());
}

// Get categories and candidates if election is active and student hasn't voted
$categories = [];
if ($currentElection && !$hasVoted) {
    try {
        // Get categories for current election
        $stmt = $conn->prepare("SELECT * FROM categories WHERE electionID = ?");
        $stmt->bind_param('i', $currentElection['electionID']);
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get candidates for each category
        foreach ($categories as &$category) {
            $stmt = $conn->prepare("
                SELECT c.*, s.name, s.department 
                FROM candidates c
                JOIN students s ON c.studentID = s.studentID
                WHERE c.categoryID = ?
            ");
            $stmt->bind_param('i', $category['categoryID']);
            $stmt->execute();
            $category['candidates'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Categories fetch error: " . $e->getMessage());
        $error = "Error loading voting categories";
    }
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote'])) {
    if (!$currentElection || $hasVoted) {
        $error = "You cannot vote at this time";
    } else {
        try {
            $conn->begin_transaction();
            
            // Validate all categories have selections
            $votes = [];
            foreach ($categories as $category) {
                if (!isset($_POST['category_'.$category['categoryID']])) {
                    throw new Exception("Please select a candidate for all positions");
                }
                
                $candidateID = (int)$_POST['category_'.$category['categoryID']];
                $votes[] = [
                    'election_id' => $currentElection['electionID'],
                    'category_id' => $category['categoryID'],
                    'candidate_id' => $candidateID,
                    'student_id' => $studentID
                ];
            }
            
            // Record votes
            $stmt = $conn->prepare("
                INSERT INTO votes 
                (election_id, category_id, candidate_id, student_id, voted_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            foreach ($votes as $vote) {
                $stmt->bind_param('iiii', 
                    $vote['election_id'],
                    $vote['category_id'],
                    $vote['candidate_id'],
                    $vote['student_id']
                );
                $stmt->execute();
            }
            
            $conn->commit();
            $success = "Your vote has been successfully recorded!";
            $hasVoted = true;
            
            // Send notification
            $notification = "Thank you for voting in the ".htmlspecialchars($currentElection['title'])." election";
            $stmt = $conn->prepare("
                INSERT INTO notifications 
                (studentID, title, message, type, is_read, created_at)
                VALUES (?, 'Vote Submitted', ?, 'vote', 0, NOW())
            ");
            $stmt->bind_param('is', $studentID, $notification);
            $stmt->execute();
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error submitting vote: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Portal - SmartVote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-light: rgba(13, 110, 253, 0.08);
            --success-light: rgba(25, 135, 84, 0.1);
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --card-hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        
        .voting-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .candidate-card {
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 10px;
            background: white;
            overflow: hidden;
            position: relative;
        }
        
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(13, 110, 253, 0.3);
        }
        
        .candidate-card.selected {
            border: 2px solid #0d6efd;
            background-color: var(--primary-light);
            box-shadow: var(--card-hover-shadow);
        }
        
        .candidate-card.selected::after {
            content: '';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background-color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .candidate-card.selected::before {
            content: '\F26E';
            font-family: bootstrap-icons;
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
            z-index: 2;
            font-size: 10px;
        }
        
        .avatar-container {
            position: relative;
            width: 90px;
            height: 90px;
            margin: 0 auto;
        }
        
        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .candidate-card.selected .avatar {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }
        
        .department-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #f8f9fa;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .category-section {
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .category-badge {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .vote-submit-btn {
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .vote-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .election-timer {
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .timer-countdown {
            font-size: 1.8rem;
            font-weight: 700;
            font-family: monospace;
        }
        
        @media (max-width: 768px) {
            .avatar-container {
                width: 70px;
                height: 70px;
            }
            
            .candidate-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?><br>
    
    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="voting-card mb-4">
                    <div class="card-header bg-white py-4 border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div class="mb-3 mb-md-0">
                                <h2 class="mb-1 fw-bold">Voting Portal</h2>
                                <p class="text-muted mb-0">Cast your vote in the current election</p>
                            </div>
                            <span class="badge bg-<?= $currentElection ? 'primary' : 'secondary' ?> px-3 py-2">
                                <?= $currentElection ? 'Election Active' : 'No Active Election' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body px-4 py-4">
                        <!-- Student Info -->
                        <div class="alert bg-light d-flex align-items-center mb-4 border-0">
                            <div class="avatar-container me-3">
                                <?php if (!empty($student['profile_picture'])): ?>
                                    <img src="assets/img/profile/students/<?= htmlspecialchars($student['profile_picture']) ?>" 
                                         class="avatar" 
                                         alt="Profile">
                                <?php else: ?>
                                    <div class="avatar bg-light d-flex align-items-center justify-content-center text-primary">
                                        <i class="bi bi-person-fill fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1"><?= htmlspecialchars($student['name'] ?? 'Student') ?></h5>
                                <p class="text-muted small mb-0">Student ID: <?= $studentID ?></p>
                                <p class="text-muted small mb-0">Deparment:<?= htmlspecialchars($student['department'] ?? '') ?></p>
                            </div>
                        </div>
                        
                        <!-- Status Messages -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-lg">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><?= $error ?></div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded-lg">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div><?= $success ?></div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Election Info -->
                        <?php if ($currentElection): ?>
                            <div class="election-timer mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <h4 class="text-white mb-1"><?= htmlspecialchars($currentElection['title']) ?></h4>
                                        <p class="text-white-50 mb-0"><?= htmlspecialchars($currentElection['description']) ?></p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="timer-countdown text-white mb-1">
                                            <?= date('M j, Y', strtotime($currentElection['end_date'])) ?>
                                        </div>
                                        <p class="text-white-50 small mb-0">
                                            <i class="bi bi-clock me-1"></i>
                                            Ends at <?= date('h:i A', strtotime($currentElection['end_time'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning rounded-lg">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-1">No Active Election</h5>
                                        <p class="mb-0">There is currently no active election. Please check back later.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Voting Form -->
                        <?php if ($currentElection && !$hasVoted): ?>
                            <form id="votingForm" method="POST">
                                <?php foreach ($categories as $category): ?>
                                    <div class="category-section">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="category-badge text-white me-3"><?= $category['name'] ?></span>
                                            <h4 class="mb-0 fw-bold"><?= $category['description'] ?></h4>
                                        </div>
                                        
                                        <div class="row g-4">
                                            <?php foreach ($category['candidates'] as $candidate): ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="candidate-card p-3 h-100"
                                                         onclick="selectCandidate(this, <?= $category['categoryID'] ?>, <?= $candidate['candidateID'] ?>)">
                                                        <div class="text-center mb-3">
                                                            <div class="avatar-container">
                                                                <?php if (!empty($candidate['photo'])): ?>
                                                                    <img src="assets/img/candidates/<?= htmlspecialchars($candidate['photo']) ?>" 
                                                                         class="avatar" 
                                                                         alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                                <?php else: ?>
                                                                    <div class="avatar bg-light d-flex align-items-center justify-content-center text-muted">
                                                                        <i class="bi bi-person fs-3"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <span class="department-badge"><?= htmlspecialchars($candidate['department']) ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="text-center">
                                                            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($candidate['name']) ?></h5>
                                                            <p class="text-primary small mb-2 fw-bold"><?= htmlspecialchars($candidate['position']) ?></p>
                                                            <p class="text-muted small mb-0"><?= htmlspecialchars($candidate['tagline'] ?? '') ?></p>
                                                        </div>
                                                        <input type="radio" 
                                                               name="category_<?= $category['categoryID'] ?>" 
                                                               value="<?= $candidate['candidateID'] ?>" 
                                                               class="d-none" 
                                                               required>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-5 pt-3">
                                    <button type="submit" name="submit_vote" class="btn btn-primary btn-lg vote-submit-btn">
                                        <i class="bi bi-check-circle me-2"></i> Submit Your Vote
                                    </button>
                                    <p class="text-muted mt-3 small">
                                        <i class="bi bi-info-circle me-1"></i> You can review your selections before final submission
                                    </p>
                                </div>
                            </form>
                        <?php elseif ($hasVoted): ?>
                            <div class="text-center py-5 px-3">
                                <div class="mb-4">
                                    <div class="avatar-container mx-auto">
                                        <div class="avatar bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="mb-3 fw-bold">Vote Submitted Successfully!</h3>
                                <p class="lead text-muted mb-4">Thank you for participating in the <?= htmlspecialchars($currentElection['title']) ?> election.</p>
                                
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="results.php?election=<?= $currentElection['electionID'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-graph-up me-1"></i> View Results
                                    </a>
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="bi bi-house me-1"></i> Return Home
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Enhanced candidate selection with animation
    function selectCandidate(element, categoryId, candidateId) {
        // Animate deselection
        document.querySelectorAll(`input[name="category_${categoryId}"]`).forEach(radio => {
            const card = radio.closest('.candidate-card');
            if (card !== element) {
                card.classList.remove('selected');
                card.style.transform = '';
                card.style.boxShadow = '';
            }
        });
        
        // Animate selection
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;
        
        // Add subtle pulse animation
        element.style.animation = 'pulse 0.5s ease';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }
    
    // Form validation with sweet alert
    document.getElementById('votingForm')?.addEventListener('submit', function(e) {
      
            const categories = <?php echo json_encode(array_column($categories, 'categoryID')); ?>;
       
        let unvotedCategories = [];
        
        categories.forEach(categoryId => {
            if (!document.querySelector(`input[name="category_${categoryId}"]:checked`)) {
                unvotedCategories.push(categoryId);
            }
        });
        
        if (unvotedCategories.length > 0) {
            e.preventDefault();
            const categoryNames = <?php echo json_encode(array_column($categories, 'name')); ?>;
            const missingCategories = unvotedCategories.map(id => {
                const category = <?= json_encode($categories) ?>.find(c => c.categoryID == id);
                return category.name;
            });
            
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Vote',
                html: `Please select a candidate for:<br><strong>${missingCategories.join(', ')}</strong>`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd'
            });
        } else {
            if (!confirm('Are you absolutely sure you want to submit your vote? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2); }
            50% { transform: translateY(-8px); box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3); }
            100% { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2); }
        }
    `;
    document.head.appendChild(style);
    </script>
    
    <!-- SweetAlert for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
