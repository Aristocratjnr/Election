<?php
session_start();

include 'configs/dbconnection.php';
include 'configs/session.php';

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

$studentID = (int)$_SESSION['login_id'];
$studentType = $_SESSION['user_type'] ?? 'student'; 

// Get unread notifications count
$unreadCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications 
                          WHERE studentID = ? AND student_type = ? AND is_read = 0");
    $stmt->bind_param('is', $studentID, $studentType);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadCount = $result->fetch_assoc()['unread'];
    $stmt->close();
} catch (Exception $e) {
    error_log("Notification count error: " . $e->getMessage());
}

// Mark notifications as read when page loads
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 
                              WHERE studentID = ? AND student_type = ? AND is_read = 0");
        $stmt->bind_param('is', $studentID, $studentType);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Mark as read error: " . $e->getMessage());
    }
}

// Get all notifications for the user with related election/candidate info
$notifications = [];
try {
    $query = "SELECT n.*, e.name AS election_name, e.status AS election_status,
                     c.position AS candidate_position, s.name AS candidate_name
              FROM notifications n
              LEFT JOIN Elections e ON n.related_election = e.electionID
              LEFT JOIN Candidates c ON n.related_candidate = c.candidateID
              LEFT JOIN Students s ON c.studentID = s.studentID
              WHERE n.studentID = ? AND n.student_type = ?
              ORDER BY n.created_at DESC 
              LIMIT 50";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $studentID, $studentType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Format notification based on type
        switch ($row['type']) {
            case 'election':
                $row['icon'] = 'bi-megaphone';
                $row['bg_class'] = 'bg-primary-light';
                break;
            case 'vote':
                $row['icon'] = 'bi-check-circle';
                $row['bg_class'] = 'bg-success-light';
                break;
            case 'result':
                $row['icon'] = 'bi-graph-up';
                $row['bg_class'] = 'bg-info-light';
                break;
            case 'candidate':
                $row['icon'] = 'bi-person-badge';
                $row['bg_class'] = 'bg-warning-light';
                break;
            default: // system/reminder
                $row['icon'] = 'bi-bell';
                $row['bg_class'] = 'bg-secondary-light';
        }
        
        $notifications[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Fetch notifications error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .notification-item.unread {
            background-color: rgba(13, 110, 253, 0.05);
            border-left-color: #0d6efd;
        }
        
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
        .bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
        .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
        .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
        .bg-secondary-light { background-color: rgba(108, 117, 125, 0.1); }
        
        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?><br>
    
    <main class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-bell-fill me-2"></i>Notifications
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2"><?= $unreadCount ?> unread</span>
                            <?php endif; ?>
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-notifications">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="empty-state py-5">
                                <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">No notifications yet</h5>
                                <p class="text-muted">Your notifications will appear here</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $notification): ?>
                                <a href="<?= htmlspecialchars($notification['action_url'] ?? '#') ?>" 
                                   class="list-group-item list-group-item-action notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle p-2 me-3 <?= $notification['bg_class'] ?>">
                                            <i class="bi <?= $notification['icon'] ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                                <small class="notification-time">
                                                    <?= (new DateTime($notification['created_at']))->format('M j, g:i a') ?>
                                                </small>
                                            </div>
                                            <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                            
                                            <?php if ($notification['election_name']): ?>
                                                <span class="badge bg-light text-dark mt-1">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <?= htmlspecialchars($notification['election_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($notification['candidate_position']): ?>
                                                <span class="badge bg-light text-dark mt-1">
                                                    <i class="bi bi-person me-1"></i>
                                                    <?= htmlspecialchars($notification['candidate_name'] ?? 'Candidate') ?> - 
                                                    <?= htmlspecialchars($notification['candidate_position']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($notifications)): ?>
                    <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-sm btn-outline-primary" id="load-more">
                                <i class="bi bi-arrow-down"></i> Load More
                            </button>
                            <small class="text-muted">
                                Showing <?= count($notifications) ?> of <?= $unreadCount + count($notifications) ?> notifications
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Refresh notifications
        $('#refresh-notifications').click(function() {
            window.location.reload();
        });
        
        // Load more notifications
        $('#load-more').click(function() {
            const $btn = $(this);
            const currentCount = <?= count($notifications) ?>;
            
            $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Loading...');
            
            $.ajax({
                url: '',
                type: 'GET',
                data: {
                    student_id: <?= $studentID ?>,
                    student_type: '<?= $studentType ?>',
                    offset: currentCount
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(function(notification) {
                            // Insert the new notification into the DOM here
                            let notificationHtml = `
                                <a href="${notification.action_url || '#'}" class="list-group-item list-group-item-action notification-item ${notification.is_read ? '' : 'unread'}">
                                    <div class="d-flex align-items-start">
                                        <div class="rounded-circle p-2 me-3 ${notification.bg_class}">
                                            <i class="bi ${notification.icon}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">${notification.title}</h6>
                                                <small class="notification-time">${notification.created_at}</small>
                                            </div>
                                            <p class="mb-1">${notification.message}</p>
                                        </div>
                                    </div>
                                </a>
                            `;
                            $('.list-group').append(notificationHtml);
                        });
                        
                        if (!data.has_more) {
                            $btn.hide();
                        }
                    } else {
                        $btn.hide();
                    }
                },
                complete: function() {
                    $btn.html('<i class="bi bi-arrow-down"></i> Load More');
                }
            });
        });
        
        // Real-time notification check
        function checkNewNotifications() {
            $.ajax({
                url: '',
                type: 'GET',
                data: {
                    student_id: <?= $studentID ?>,
                    student_type: '<?= $studentType ?>',
                    last_check: new Date().toISOString()
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.count > 0) {
                        alert('You have new notifications!');
                    }
                }
            });
        }
        
        // Check every 30 seconds
        setInterval(checkNewNotifications, 30000);
    });
    </script>
</body>
</html>
