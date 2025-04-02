<?php
include 'configs/dbconnection.php';
include 'configs/session.php';

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['login_id']; 


$unreadCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications 
                          WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $userId);
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
                              WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Mark as read error: " . $e->getMessage());
    }
}

// Get all notifications for the user
$notifications = [];
try {
    $stmt = $conn->prepare("SELECT * FROM notifications 
                           WHERE user_id = ? 
                           ORDER BY created_at DESC 
                           LIMIT 50");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
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
    <title>Notifications</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --notification-info: #0dcaf0;
            --notification-success: #198754;
            --notification-warning: #ffc107;
            --notification-danger: #dc3545;
            --notification-primary: #0d6efd;
        }
        
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .notification-item.unread {
            background-color: #f0f8ff;
            border-left-color: var(--notification-primary);
        }
        
        .notification-item.info {
            border-left-color: var(--notification-info);
        }
        
        .notification-item.success {
            border-left-color: var(--notification-success);
        }
        
        .notification-item.warning {
            border-left-color: var(--notification-warning);
        }
        
        .notification-item.danger {
            border-left-color: var(--notification-danger);
        }
        
        .notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .notification-dot.unread {
            background-color: var(--notification-primary);
        }
        
        .notification-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
        }
        
        .notification-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #6c757d;
        }
        
        .notification-icon.info {
            color: var(--notification-info);
        }
        
        .notification-icon.success {
            color: var(--notification-success);
        }
        
        .notification-icon.warning {
            color: var(--notification-warning);
        }
        
        .notification-icon.danger {
            color: var(--notification-danger);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Your App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notifications.php">
                            Notifications
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Notifications</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="mark-all-read">
                                <i class="bi bi-check-all"></i> Mark all as read
                            </button>
                            <button class="btn btn-sm btn-outline-danger ms-2" id="clear-notifications">
                                <i class="bi bi-trash"></i> Clear all
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="empty-state">
                                <i class="bi bi-bell"></i>
                                <h5>No notifications yet</h5>
                                <p>When you get notifications, they'll appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush" id="notification-list">
                                <?php foreach ($notifications as $notification): 
                                    $typeClass = '';
                                    $icon = 'bi-bell';
                                    
                                    switch ($notification['type']) {
                                        case 'info':
                                            $typeClass = 'info';
                                            $icon = 'bi-info-circle';
                                            break;
                                        case 'success':
                                            $typeClass = 'success';
                                            $icon = 'bi-check-circle';
                                            break;
                                        case 'warning':
                                            $typeClass = 'warning';
                                            $icon = 'bi-exclamation-triangle';
                                            break;
                                        case 'danger':
                                            $typeClass = 'danger';
                                            $icon = 'bi-x-circle';
                                            break;
                                        default:
                                            $typeClass = '';
                                            $icon = 'bi-bell';
                                    }
                                ?>
                                <a href="<?php echo htmlspecialchars($notification['action_url'] ?? '#'); ?>" 
                                   class="list-group-item list-group-item-action notification-item <?php echo $typeClass; ?> <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-icon <?php echo $typeClass; ?>">
                                            <i class="bi <?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <small class="notification-time">
                                                    <?php 
                                                        $date = new DateTime($notification['created_at']);
                                                        echo $date->format('M j, Y g:i A');
                                                    ?>
                                                </small>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="notification-dot unread"></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($notifications)): ?>
                    <div class="card-footer text-center">
                        <button class="btn btn-sm btn-outline-primary" id="load-more">
                            <i class="bi bi-arrow-down"></i> Load more
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> Your App. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Mark all as read
        $('#mark-all-read').click(function() {
            $.ajax({
                url: 'actions/mark_all_read.php',
                type: 'POST',
                data: { user_id: <?php echo $userId; ?> },
                success: function(response) {
                    if (response.success) {
                        $('.notification-item').removeClass('unread');
                        $('.notification-dot').remove();
                        updateUnreadCount(0);
                    }
                },
                error: function() {
                    alert('Error marking notifications as read');
                }
            });
        });
        
        // Clear all notifications
        $('#clear-notifications').click(function() {
            if (confirm('Are you sure you want to clear all notifications? This cannot be undone.')) {
                $.ajax({
                    url: 'actions/clear_notifications.php',
                    type: 'POST',
                    data: { user_id: <?php echo $userId; ?> },
                    success: function(response) {
                        if (response.success) {
                            $('#notification-list').html(`
                                <div class="empty-state">
                                    <i class="bi bi-bell"></i>
                                    <h5>No notifications yet</h5>
                                    <p>When you get notifications, they'll appear here.</p>
                                </div>
                            `);
                            $('.card-footer').remove();
                            updateUnreadCount(0);
                        }
                    },
                    error: function() {
                        alert('Error clearing notifications');
                    }
                });
            }
        });
        
        // Load more notifications
        let loading = false;
        let offset = <?php echo count($notifications); ?>;
        
        $('#load-more').click(function() {
            if (loading) return;
            
            loading = true;
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            
            $.ajax({
                url: 'actions/load_more_notifications.php',
                type: 'GET',
                data: { 
                    user_id: <?php echo $userId; ?>,
                    offset: offset
                },
                success: function(response) {
                    if (response.notifications && response.notifications.length > 0) {
                        response.notifications.forEach(function(notification) {
                            let typeClass = '';
                            let icon = 'bi-bell';
                            
                            switch (notification.type) {
                                case 'info':
                                    typeClass = 'info';
                                    icon = 'bi-info-circle';
                                    break;
                                case 'success':
                                    typeClass = 'success';
                                    icon = 'bi-check-circle';
                                    break;
                                case 'warning':
                                    typeClass = 'warning';
                                    icon = 'bi-exclamation-triangle';
                                    break;
                                case 'danger':
                                    typeClass = 'danger';
                                    icon = 'bi-x-circle';
                                    break;
                            }
                            
                            const date = new Date(notification.created_at);
                            const formattedDate = date.toLocaleString('en-US', { 
                                month: 'short', 
                                day: 'numeric', 
                                year: 'numeric',
                                hour: 'numeric', 
                                minute: '2-digit' 
                            });
                            
                            const unreadDot = notification.is_read ? '' : '<span class="notification-dot unread"></span>';
                            
                            $('#notification-list').append(`
                                <a href="${notification.action_url || '#'}" 
                                   class="list-group-item list-group-item-action notification-item ${typeClass} ${notification.is_read ? '' : 'unread'}">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-icon ${typeClass}">
                                            <i class="bi ${icon}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">${notification.title}</h6>
                                                <small class="notification-time">
                                                    ${formattedDate}
                                                </small>
                                            </div>
                                            <p class="mb-1">${notification.message}</p>
                                            ${unreadDot}
                                        </div>
                                    </div>
                                </a>
                            `);
                        });
                        
                        offset += response.notifications.length;
                        
                        if (response.has_more === false) {
                            $('#load-more').hide();
                        }
                    } else {
                        $('#load-more').hide();
                    }
                },
                complete: function() {
                    loading = false;
                    $('#load-more').html('<i class="bi bi-arrow-down"></i> Load more');
                }
            });
        });
        
        // Function to update unread count in navbar
        function updateUnreadCount(count) {
            const badge = $('.nav-link.active .badge');
            if (count > 0) {
                if (badge.length) {
                    badge.text(count);
                } else {
                    $('.nav-link.active').append(`<span class="badge bg-danger rounded-pill">${count}</span>`);
                }
            } else {
                badge.remove();
            }
        }
        
        // Check for new notifications periodically
        setInterval(function() {
            $.ajax({
                url: 'actions/check_new_notifications.php',
                type: 'GET',
                data: { user_id: <?php echo $userId; ?> },
                success: function(response) {
                    if (response.count > 0) {
                        updateUnreadCount(response.count);
                        
                        // Optional: Show toast for new notifications
                        if (response.latest_notification) {
                            showNewNotificationToast(response.latest_notification);
                        }
                    }
                }
            });
        }, 30000); // Check every 30 seconds
        
        // Function to show toast for new notification
        function showNewNotificationToast(notification) {
            let typeClass = '';
            let icon = 'bi-bell';
            
            switch (notification.type) {
                case 'info':
                    typeClass = 'info';
                    icon = 'bi-info-circle';
                    break;
                case 'success':
                    typeClass = 'success';
                    icon = 'bi-check-circle';
                    break;
                case 'warning':
                    typeClass = 'warning';
                    icon = 'bi-exclamation-triangle';
                    break;
                case 'danger':
                    typeClass = 'danger';
                    icon = 'bi-x-circle';
                    break;
            }
            
            const toast = $(`
                <div class="toast show align-items-center text-white bg-${typeClass} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <div class="d-flex align-items-center">
                                <i class="bi ${icon} me-2" style="font-size: 1.5rem;"></i>
                                <div>
                                    <strong>${notification.title}</strong>
                                    <div>${notification.message}</div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                toast.remove();
            }, 5000);
            
            // Click handler
            toast.on('click', function() {
                window.location.href = notification.action_url || 'notifications.php';
            });
        }
    });
    </script>
</body>
</html>