<?php
session_start();

include 'configs/dbconnection.php';
include 'configs/session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

$userID = (int)$_SESSION['login_id'];
$userType = $_SESSION['user_type'] ?? 'student'; 

// Get unread notifications count
$unreadCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications 
                          WHERE user_id = ? AND user_type = ? AND is_read = 0");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('is', $userID, $userType);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $unreadCount = $result->fetch_assoc()['unread'];
    $stmt->close();
} catch (Exception $e) {
    error_log("Notification count error: " . $e->getMessage());
    $unreadCount = 0;
}

// Mark notifications as read when page loads
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 
                              WHERE user_id = ? AND user_type = ? AND is_read = 0");
        if ($stmt) {
            $stmt->bind_param('is', $userID, $userType);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Mark as read error: " . $e->getMessage());
    }
}

// Get all notifications
$notifications = [];
try {
    $query = "SELECT n.*, e.name AS election_name, e.status AS election_status,
                     c.position AS candidate_position, s.name AS candidate_name
              FROM notifications n
              LEFT JOIN elections e ON n.related_election = e.electionID
              LEFT JOIN candidates c ON n.related_candidate = c.candidateID
              LEFT JOIN students s ON c.studentID = s.studentID
              WHERE n.user_id = ? AND n.user_type = ?
              ORDER BY n.created_at DESC 
              LIMIT 50";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('is', $userID, $userType);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Format notification
        switch ($row['type']) {
            case 'election':
                $row['icon'] = 'bi-megaphone';
                $row['bg_class'] = 'bg-primary-light';
                $row['badge_class'] = 'bg-primary';
                break;
            case 'vote':
                $row['icon'] = 'bi-check-circle';
                $row['bg_class'] = 'bg-success-light';
                $row['badge_class'] = 'bg-success';
                break;
            case 'result':
                $row['icon'] = 'bi-graph-up';
                $row['bg_class'] = 'bg-info-light';
                $row['badge_class'] = 'bg-info';
                break;
            case 'candidate':
                $row['icon'] = 'bi-person-badge';
                $row['bg_class'] = 'bg-warning-light';
                $row['badge_class'] = 'bg-warning';
                break;
            default:
                $row['icon'] = 'bi-bell';
                $row['bg_class'] = 'bg-secondary-light';
                $row['badge_class'] = 'bg-secondary';
        }
        
        // Format time as "X minutes/hours/days ago"
        $createdAt = new DateTime($row['created_at']);
        $now = new DateTime();
        $interval = $now->diff($createdAt);
        
        if ($interval->d > 0) {
            $row['time_ago'] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $row['time_ago'] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } else {
            $row['time_ago'] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        }
        
        $notifications[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Fetch notifications error: " . $e->getMessage());
    $notifications = [];
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
        :root {
            --notification-primary: #7367f0;
            --notification-success: #28c76f;
            --notification-info: #00cfe8;
            --notification-warning: #ff9f43;
            --notification-secondary: #82868b;
        }
        
        .notification-container {
            max-height: 70vh;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
        }
        
        .notification-item.unread {
            background-color: rgba(115, 103, 240, 0.05);
            border-left-color: var(--notification-primary);
        }
        
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .bg-primary-light { background-color: rgba(115, 103, 240, 0.1); }
        .bg-success-light { background-color: rgba(40, 199, 111, 0.1); }
        .bg-info-light { background-color: rgba(0, 207, 232, 0.1); }
        .bg-warning-light { background-color: rgba(255, 159, 67, 0.1); }
        .bg-secondary-light { background-color: rgba(130, 134, 139, 0.1); }
        
        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .notification-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .notification-content {
            flex-grow: 1;
        }
        
        /* Custom scrollbar */
        .notification-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .notification-container::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.1);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br><br>
    
    <main class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                        <h5 class="mb-0 fw-semibold text-muted">
                            <i class="bi bi-bell-fill me-2 text-secondary"></i>Notifications
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2"><?= $unreadCount ?> new</span>
                            <?php endif; ?>
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" id="refresh-notifications">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="empty-state py-5">
                                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">No notifications yet</h5>
                                <p class="text-muted">When you get notifications, they'll appear here</p>
                            </div>
                        <?php else: ?>
                            <div class="notification-container">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notifications as $notification): ?>
                                    <a href="<?= htmlspecialchars($notification['action_url'] ?? '#') ?>" 
                                       class="list-group-item list-group-item-action notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="notification-badge <?= $notification['badge_class'] ?>"></span>
                                        <?php endif; ?>
                                        <div class="d-flex align-items-start p-3">
                                            <div class="notification-icon <?= $notification['bg_class'] ?>">
                                                <i class="bi <?= $notification['icon'] ?> fs-5"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($notification['title']) ?></h6>
                                                    <small class="notification-time">
                                                        <?= $notification['time_ago'] ?>
                                                    </small>
                                                </div>
                                                <p class="mb-2 text-muted"><?= htmlspecialchars($notification['message']) ?></p>
                                                
                                                <?php if ($notification['election_name']): ?>
                                                    <span class="badge <?= $notification['badge_class'] ?> text-white mt-1 me-1">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        <?= htmlspecialchars($notification['election_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($notification['candidate_position']): ?>
                                                    <span class="badge <?= $notification['badge_class'] ?> text-white mt-1">
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
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($notifications)): ?>
                    <div class="card-footer bg-white py-3 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-outline-primary" id="load-more">
                                <i class="bi bi-arrow-down me-1"></i> Load More
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
    </main><br><br><br><br><br><br><br><br><br>

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
            
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Loading...');
            $btn.prop('disabled', true);
            
            $.ajax({
                url: 'api/notifications.php',
                type: 'GET',
                data: {
                    offset: currentCount,
                    user_id: <?= $userID ?>,
                    user_type: '<?= $userType ?>'
                },
                success: function(data) {
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(function(notification) {
                            const notificationHtml = `
                                <a href="${notification.action_url || '#'}" 
                                   class="list-group-item list-group-item-action notification-item ${notification.is_read ? '' : 'unread'}">
                                    ${!notification.is_read ? `<span class="notification-badge ${notification.badge_class}"></span>` : ''}
                                    <div class="d-flex align-items-start p-3">
                                        <div class="notification-icon ${notification.bg_class}">
                                            <i class="bi ${notification.icon} fs-5"></i>
                                        </div>
                                        <div class="notification-content">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold">${notification.title}</h6>
                                                <small class="notification-time">${notification.time_ago}</small>
                                            </div>
                                            <p class="mb-2 text-muted">${notification.message}</p>
                                            ${notification.election_name ? `
                                                <span class="badge ${notification.badge_class} text-white mt-1 me-1">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    ${notification.election_name}
                                                </span>
                                            ` : ''}
                                            ${notification.candidate_position ? `
                                                <span class="badge ${notification.badge_class} text-white mt-1">
                                                    <i class="bi bi-person me-1"></i>
                                                    ${notification.candidate_name || 'Candidate'} - ${notification.candidate_position}
                                                </span>
                                            ` : ''}
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
                    $btn.html('<i class="bi bi-arrow-down me-1"></i> Load More');
                    $btn.prop('disabled', false);
                },
                error: function() {
                    alert('Error loading more notifications');
                }
            });
        });
        
        // Real-time notification check
        function checkNewNotifications() {
            $.ajax({
                url: 'api/notifications_count.php',
                type: 'GET',
                data: {
                    user_id: <?= $userID ?>,
                    user_type: '<?= $userType ?>',
                    last_check: new Date().toISOString()
                },
                success: function(data) {
                    if (data.count > 0) {
                        // Show subtle notification badge instead of alert
                        const $badge = $('#notification-badge');
                        $badge.text(data.count).removeClass('d-none');
                        
                        // Optional: Show toast notification
                        if (data.latest_notification) {
                            showToastNotification(data.latest_notification);
                        }
                    }
                }
            });
        }
        
        // Show toast notification
        function showToastNotification(notification) {
            const toastHtml = `
                <div class="toast show position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 9999">
                    <div class="toast-header">
                        <strong class="me-auto">New Notification</strong>
                        <small>Just now</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${notification.message}
                    </div>
                </div>
            `;
            $('body').append(toastHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $('.toast').remove();
            }, 5000);
        }
        
        // Check every 30 seconds
        setInterval(checkNewNotifications, 30000);
    });
    </script>
</body>
</html>