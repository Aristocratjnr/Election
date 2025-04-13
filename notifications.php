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
$userType = $_SESSION['role'] ?? 'student'; 

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
                     p.title AS position_title, s.name AS candidate_name
              FROM notifications n
              LEFT JOIN elections e ON n.related_election = e.electionID
              LEFT JOIN candidates c ON n.related_candidate = c.candidateID
              LEFT JOIN positions p ON c.positionID = p.positionID
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
            case 'system':
                $row['icon'] = 'bi-gear';
                $row['bg_class'] = 'bg-secondary-light';
                $row['badge_class'] = 'bg-secondary';
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
        
        if ($interval->y > 0) {
            $row['time_ago'] = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            $row['time_ago'] = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            $row['time_ago'] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $row['time_ago'] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $row['time_ago'] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $row['time_ago'] = 'Just now';
        }
        
        $notifications[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Fetch notifications error: " . $e->getMessage());
    $notifications = [];
}

// Get notification links based on type
function getNotificationLink($notification) {
    switch ($notification['type']) {
        case 'vote':
            return 'live_results.php?election=' . $notification['related_election'];
        case 'election':
            return 'student.php';
        case 'result':
            return 'live_results.php?election=' . $notification['related_election'];
        case 'candidate':
            return 'student.php';
        default:
            return '#';
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Voting System</title>  
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --notification-primary: #4361ee;
            --notification-success: #28c76f;
            --notification-info: #00cfe8;
            --notification-warning: #ff9f43;
            --notification-secondary: #82868b;
            --notification-danger: #ea5455;
        }
        
        /* Container styles */
        .notification-container {
            max-height: calc(100vh - 250px);
            overflow-y: auto;
            scrollbar-width: thin;
            border-radius: 0.5rem;
        }
        
        /* Notification item styles */
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
            padding: 1rem 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .notification-item.unread {
            background-color: rgba(67, 97, 238, 0.05);
            border-left-color: var(--notification-primary);
        }
        
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            z-index: 1;
        }
        
        /* Icon styles */
        .notification-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        /* Background colors */
        .bg-primary-light { background-color: rgba(67, 97, 238, 0.15); }
        .bg-success-light { background-color: rgba(40, 199, 111, 0.15); }
        .bg-info-light { background-color: rgba(0, 207, 232, 0.15); }
        .bg-warning-light { background-color: rgba(255, 159, 67, 0.15); }
        .bg-secondary-light { background-color: rgba(130, 134, 139, 0.15); }
        .bg-danger-light { background-color: rgba(234, 84, 85, 0.15); }
        
        /* Text and badge styles */
        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
            display: flex;
            align-items: center;
        }
        
        .notification-time i {
            margin-right: 0.25rem;
            font-size: 0.7rem;
        }
        
        .notification-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .notification-content {
            flex-grow: 1;
            min-width: 0; /* Prevents text overflow */
        }
        
        /* Empty state styles */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dfe3e7;
            margin-bottom: 1rem;
            display: block;
        }
        
        /* Card styles */
        .card {
            border-radius: 0.75rem;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 1rem 1.5rem;
        }
        
        /* Button styles */
        .btn-refresh, .btn-load-more {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .btn-refresh i, .btn-load-more i {
            margin-right: 0.35rem;
        }
        
        .btn-refresh:hover, .btn-load-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(67, 97, 238, 0.2);
        }
        
        /* Badge styles for categories */
        .category-badge {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .category-badge i {
            margin-right: 0.35rem;
            font-size: 0.8rem;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .notification-container {
                max-height: calc(100vh - 200px);
            }
            
            .notification-item {
                padding: 0.75rem 1rem;
            }
            
            .notification-icon {
                width: 38px;
                height: 38px;
                margin-right: 10px;
            }
            
            .notification-content h6 {
                font-size: 0.9rem;
            }
            
            .notification-content p {
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }
            
            .card-header {
                padding: 0.75rem 1rem;
            }
            
            .card-header h5 {
                font-size: 1.1rem;
            }
        }
        
        /* Larger screens */
        @media (min-width: 769px) {
            .container {
                max-width: 960px;
            }
        }
        
        /* Custom scrollbar */
        .notification-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .notification-container::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.1);
            border-radius: 3px;
        }
        
        /* Animation for new notifications */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .notification-new {
            animation: fadeIn 0.5s ease forwards;
        }
        
        /* Card footer */
        .card-footer {
            background-color: #f8f9fa;
            padding: 0.75rem 1.5rem;
        }
        
        /* Dark mode styles */
        [data-bs-theme="dark"] {
            --notification-primary: #7367f0;
            --notification-success: #28c76f;
            --notification-info: #00cfe8;
            --notification-warning: #ff9f43;
            --notification-secondary: #82868b;
            --notification-danger: #ea5455;
        }
        
        [data-bs-theme="dark"] .card {
            background-color: #2b3035;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        [data-bs-theme="dark"] .card-header {
            background-color: #343a40 !important;
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }
        
        [data-bs-theme="dark"] .card-footer {
            background-color: #343a40;
            border-top-color: rgba(255, 255, 255, 0.08);
        }
        
        [data-bs-theme="dark"] .notification-item {
            background-color: #2b3035;
            border-color: #2b3035;
        }
        
        [data-bs-theme="dark"] .notification-item:hover {
            background-color: #343a40;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }
        
        [data-bs-theme="dark"] .notification-item.unread {
            background-color: rgba(115, 103, 240, 0.1);
            border-left-color: var(--notification-primary);
        }
        
        [data-bs-theme="dark"] .notification-time,
        [data-bs-theme="dark"] .text-muted {
            color: #adb5bd !important;
        }
        
        [data-bs-theme="dark"] .empty-state {
            color: #adb5bd;
        }
        
        [data-bs-theme="dark"] .empty-state i {
            color: #495057;
        }
        
        [data-bs-theme="dark"] .bg-white {
            background-color: #343a40 !important;
        }
        
        [data-bs-theme="dark"] .btn-outline-primary {
            color: #7367f0;
            border-color: #7367f0;
        }
        
        [data-bs-theme="dark"] .btn-outline-primary:hover {
            background-color: #7367f0;
            color: #fff;
        }
        
        [data-bs-theme="dark"] .list-group-item {
            background-color: #2b3035;
            border-color: rgba(255, 255, 255, 0.08);
        }
        
        [data-bs-theme="dark"] .notification-container::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br><br>
    
    <main class="container-fluid container-lg my-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-bell-fill me-2 text-primary"></i>Notifications
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-2">
                                    <i class="bi bi-envelope-exclamation-fill me-1"></i><?= $unreadCount ?> new
                                </span>
                            <?php endif; ?>
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-primary btn-refresh" id="refresh-notifications">
                                <i class="bi bi-arrow-repeat"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="empty-state py-5">
                                <i class="bi bi-bell-slash text-muted"></i>
                                <h5 class="mt-3 fw-bold">No notifications yet</h5>
                                <p class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>When you get notifications, they'll appear here
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="notification-container">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notifications as $notification): ?>
                                    <a href="<?= getNotificationLink($notification) ?>" class="list-group-item list-group-item-action notification-item <?= $notification['is_read'] ? '' : 'unread' ?>"
                                         data-notification-id="<?= $notification['notification_id'] ?>">
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="notification-badge <?= $notification['badge_class'] ?>"></span>
                                        <?php endif; ?>
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon <?= $notification['bg_class'] ?>">
                                                <i class="bi <?= $notification['icon'] ?> fs-5"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($notification['title']) ?></h6>
                                                    <small class="notification-time">
                                                        <i class="bi bi-clock"></i> <?= $notification['time_ago'] ?>
                                                    </small>
                                                </div>
                                                <p class="mb-2 text-muted"><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
                                                
                                                <div class="d-flex flex-wrap mt-2">
                                                    <?php if ($notification['election_name']): ?>
                                                        <span class="category-badge <?= $notification['badge_class'] ?> text-white">
                                                            <i class="bi bi-calendar-event"></i>
                                                            <?= htmlspecialchars($notification['election_name']) ?>
                                                            <?php if ($notification['election_status']): ?>
                                                                <span class="ms-1">
                                                                    <?php if ($notification['election_status'] == 'Ongoing'): ?>
                                                                        <i class="bi bi-play-fill"></i>
                                                                    <?php elseif ($notification['election_status'] == 'Completed'): ?>
                                                                        <i class="bi bi-check2-circle"></i>
                                                                    <?php elseif ($notification['election_status'] == 'Scheduled'): ?>
                                                                        <i class="bi bi-hourglass-split"></i>
                                                                    <?php endif; ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($notification['position_title']): ?>
                                                        <span class="category-badge <?= $notification['badge_class'] ?> text-white">
                                                            <i class="bi bi-person-badge"></i>
                                                            <?= htmlspecialchars($notification['candidate_name'] ?? 'Candidate') ?> - 
                                                            <?= htmlspecialchars($notification['position_title']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($notifications) && count($notifications) >= 50): ?>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <button class="btn btn-sm btn-outline-primary btn-load-more" id="load-more">
                            <i class="bi bi-arrow-down-circle"></i> Load More
                        </button>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Showing <?= count($notifications) ?> notifications
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>



    <!-- Audio element for notification sound -->
    <audio id="notification-sound" preload="auto">
        <source src="assets\audio\sounds\notification.mp3" type="audio/mpeg">
    </audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Audio setup
        const notificationSound = document.getElementById('notification-sound');
        
        // Function to play notification sound
        function playNotificationSound() {
            if (notificationSound) {
                notificationSound.currentTime = 0;
                notificationSound.play().catch(err => {
                    console.log('Audio playback was prevented:', err);
                });
            }
        }

        // Apply theme from localStorage
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        
        // Listen for theme change events from header
        document.addEventListener('themeChanged', function(e) {
            document.documentElement.setAttribute('data-bs-theme', e.detail.theme);
        });
        
        // Update class for dark mode compatibility
        function updateDarkModeClasses() {
            const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            // Update card header class
            const cardHeader = document.querySelector('.card-header.bg-white');
            if (cardHeader) {
                if (isDarkMode) {
                    cardHeader.classList.remove('bg-white');
                    cardHeader.classList.add('bg-dark-subtle');
                } else {
                    cardHeader.classList.add('bg-white');
                    cardHeader.classList.remove('bg-dark-subtle');
                }
            }
        }
        
        // Run initially
        updateDarkModeClasses();
        
        // Run when theme changes
        document.addEventListener('themeChanged', function() {
            updateDarkModeClasses();
        });
        
        // Refresh notifications with AJAX
        document.getElementById('refresh-notifications').addEventListener('click', function() {
            const btn = this;
            const userID = <?= $userID ?>;
            const userType = '<?= $userType ?>';
            const previousCount = document.querySelectorAll('.notification-item.unread').length;
            
            // Change button state to loading
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Refreshing...';
            btn.disabled = true;
            
            // Add timestamp to prevent caching
            const timestamp = new Date().getTime();
            
            // Fetch fresh notifications
            fetch(`api/notifications.php?user_id=${userID}&user_type=${userType}&t=${timestamp}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.querySelector('.notification-container');
                        const listGroup = document.querySelector('.list-group');
                        const emptyState = document.querySelector('.empty-state');
                        
                        if (data.notifications && data.notifications.length > 0) {
                            // Count unread notifications
                            const unreadCount = data.notifications.filter(n => n.is_read == 0).length;
                            
                            // Play sound if there are new unread notifications
                            if (unreadCount > previousCount) {
                                playNotificationSound();
                            }
                            
                            // Hide empty state if visible
                            if (emptyState) emptyState.style.display = 'none';
                            
                            // Clear existing notifications
                            listGroup.innerHTML = '';
                            
                            // Add new notifications with animation
                            data.notifications.forEach(notification => {
                                const notificationItem = document.createElement('a');
                                notificationItem.href = getNotificationLink(notification);
                                notificationItem.className = `list-group-item list-group-item-action notification-item notification-new ${notification.is_read ? '' : 'unread'}`;
                                notificationItem.dataset.notificationId = notification.notification_id;
                                
                                // Add notification badge for unread
                                let badgeHtml = '';
                                if (notification.is_read == 0) {
                                    badgeHtml = `<span class="notification-badge ${notification.badge_class}"></span>`;
                                }
                                
                                // Format notification content
                                notificationItem.innerHTML = `
                                    ${badgeHtml}
                                    <div class="d-flex align-items-start">
                                        <div class="notification-icon ${notification.bg_class}">
                                            <i class="bi ${notification.icon} fs-5"></i>
                                        </div>
                                        <div class="notification-content">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold">${escapeHtml(notification.title)}</h6>
                                                <small class="notification-time">
                                                    <i class="bi bi-clock"></i> ${notification.time_ago}
                                                </small>
                                            </div>
                                            <p class="mb-2 text-muted">${escapeHtml(notification.message)}</p>
                                            
                                            <div class="d-flex flex-wrap mt-2">
                                                ${notification.election_name ? `
                                                    <span class="category-badge ${notification.badge_class} text-white">
                                                        <i class="bi bi-calendar-event"></i>
                                                        ${escapeHtml(notification.election_name)}
                                                        ${notification.election_status ? `
                                                            <span class="ms-1">
                                                                ${notification.election_status === 'Ongoing' ? `<i class="bi bi-play-fill"></i>` : ''}
                                                                ${notification.election_status === 'Completed' ? `<i class="bi bi-check2-circle"></i>` : ''}
                                                                ${notification.election_status === 'Scheduled' ? `<i class="bi bi-hourglass-split"></i>` : ''}
                                                            </span>
                                                        ` : ''}
                                                    </span>
                                                ` : ''}
                                                
                                                ${notification.position_title ? `
                                                    <span class="category-badge ${notification.badge_class} text-white">
                                                        <i class="bi bi-person-badge"></i>
                                                        ${escapeHtml(notification.candidate_name || 'Candidate')} - 
                                                        ${escapeHtml(notification.position_title)}
                                                    </span>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                
                                // Setup click event to mark as read
                                notificationItem.addEventListener('click', function() {
                                    markNotificationAsRead(notification.notification_id, this);
                                });
                                
                                // Add to list
                                listGroup.appendChild(notificationItem);
                            });
                            
                            // Show container
                            container.style.display = 'block';
                            
                            // Update unread count in header badge
                            updateUnreadCount(data.unread || 0);
                            
                            // Show success message
                            showToast('Notifications refreshed', 'success');
                        } else {
                            // Show empty state
                            if (listGroup) listGroup.innerHTML = '';
                            if (emptyState) {
                                emptyState.style.display = 'block';
                                // Update empty state message
                                const messageEl = emptyState.querySelector('p');
                                if (messageEl) {
                                    messageEl.innerHTML = '<i class="bi bi-info-circle me-1"></i>No notifications found';
                                }
                            }
                        }
                        
                        // Update dark mode styles
                        updateDarkModeClasses();
                    } else {
                        showToast('Error refreshing notifications', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error refreshing notifications:', error);
                    showToast('Network error, please try again', 'error');
                })
                .finally(() => {
                    // Reset button state after short delay
                    setTimeout(() => {
                        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Refresh';
                        btn.disabled = false;
                    }, 500);
                });
        });
        
        // Load more notifications
        const loadMoreBtn = document.getElementById('load-more');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const btn = this;
                const currentCount = <?= count($notifications) ?>;
                const userID = <?= $userID ?>;
                const previousUnreadCount = document.querySelectorAll('.notification-item.unread').length;
                
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Loading...';
                btn.disabled = true;
                
                // Fetch more notifications
                fetch(`api/notifications.php?offset=${currentCount}&user_id=${userID}&user_type=<?= $userType ?>`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.notifications && data.notifications.length > 0) {
                            const listGroup = document.querySelector('.list-group');
                            
                            // Count new unread notifications
                            const newUnreadCount = data.notifications.filter(n => n.is_read == 0).length;
                            
                            // Play sound if there are new unread notifications
                            if (newUnreadCount > 0) {
                                playNotificationSound();
                            }
                            
                            data.notifications.forEach(function(notification) {
                                // Create notification element
                                const notificationItem = document.createElement('a');
                                notificationItem.href = getNotificationLink(notification);
                                notificationItem.className = `list-group-item list-group-item-action notification-item notification-new ${notification.is_read ? '' : 'unread'}`;
                                notificationItem.dataset.notificationId = notification.notification_id;
                                
                                // Add notification badge for unread
                                let badgeHtml = '';
                                if (!notification.is_read) {
                                    badgeHtml = `<span class="notification-badge ${notification.badge_class}"></span>`;
                                }
                                
                                // Format notification content
                                notificationItem.innerHTML = `
                                    ${badgeHtml}
                                    <div class="d-flex align-items-start">
                                        <div class="notification-icon ${notification.bg_class}">
                                            <i class="bi ${notification.icon} fs-5"></i>
                                        </div>
                                        <div class="notification-content">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold">${escapeHtml(notification.title)}</h6>
                                                <small class="notification-time">
                                                    <i class="bi bi-clock"></i> ${notification.time_ago}
                                                </small>
                                            </div>
                                            <p class="mb-2 text-muted">${escapeHtml(notification.message)}</p>
                                            
                                            <div class="d-flex flex-wrap mt-2">
                                                ${notification.election_name ? `
                                                    <span class="category-badge ${notification.badge_class} text-white">
                                                        <i class="bi bi-calendar-event"></i>
                                                        ${escapeHtml(notification.election_name)}
                                                        ${notification.election_status ? `
                                                            <span class="ms-1">
                                                                ${notification.election_status === 'Ongoing' ? `<i class="bi bi-play-fill"></i>` : ''}
                                                                ${notification.election_status === 'Completed' ? `<i class="bi bi-check2-circle"></i>` : ''}
                                                                ${notification.election_status === 'Scheduled' ? `<i class="bi bi-hourglass-split"></i>` : ''}
                                                            </span>
                                                        ` : ''}
                                                    </span>
                                                ` : ''}
                                                
                                                ${notification.position_title ? `
                                                    <span class="category-badge ${notification.badge_class} text-white">
                                                        <i class="bi bi-person-badge"></i>
                                                        ${escapeHtml(notification.candidate_name || 'Candidate')} - 
                                                        ${escapeHtml(notification.position_title)}
                                                    </span>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                
                                // Setup click handler
                                notificationItem.addEventListener('click', function() {
                                    markNotificationAsRead(notification.notification_id, this);
                                });
                                
                                // Add to list
                                listGroup.appendChild(notificationItem);
                            });
                            
                            // Update dark mode styles
                            updateDarkModeClasses();
                            
                            // Update button state
                            if (!data.has_more) {
                                btn.innerHTML = '<i class="bi bi-check-circle"></i> All loaded';
                                btn.disabled = true;
                                setTimeout(() => {
                                    btn.parentElement.style.display = 'none';
                                }, 2000);
                            } else {
                                btn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Load More';
                                btn.disabled = false;
                            }
                        } else {
                            btn.innerHTML = '<i class="bi bi-check-circle"></i> No more notifications';
                            btn.disabled = true;
                            setTimeout(() => {
                                btn.parentElement.style.display = 'none';
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more notifications:', error);
                        btn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error loading';
                        setTimeout(() => {
                            btn.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Try Again';
                            btn.disabled = false;
                        }, 2000);
                    });
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.toast-container');
            existingToasts.forEach(toast => toast.remove());
            
            // Create toast container
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            
            // Set toast class based on type
            let bgColor = 'bg-primary text-white';
            let icon = 'bi-info-circle';
            
            if (type === 'success') {
                bgColor = 'bg-success text-white';
                icon = 'bi-check-circle';
            } else if (type === 'error') {
                bgColor = 'bg-danger text-white';
                icon = 'bi-exclamation-triangle';
            }
            
            // Create toast HTML
            toastContainer.innerHTML = `
                <div class="toast show ${bgColor}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header ${bgColor}">
                        <i class="bi ${icon} me-2"></i>
                        <strong class="me-auto">Notification</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${escapeHtml(message)}
                    </div>
                </div>
            `;
            
            // Add to document
            document.body.appendChild(toastContainer);
            
            // Auto-hide toast after 3 seconds
            setTimeout(() => {
                const toast = toastContainer.querySelector('.toast');
                if (toast) {
                    toast.classList.remove('show');
                    setTimeout(() => toastContainer.remove(), 300);
                }
            }, 3000);
        }
        
        // Update unread count in header
        function updateUnreadCount(count) {
            const badge = document.querySelector('.card-header .badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count + ' new';
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        // Mark notification as read
        function markNotificationAsRead(notificationId, element) {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    if (element) {
                        element.classList.remove('unread');
                        const badge = element.querySelector('.notification-badge');
                        if (badge) badge.remove();
                    }
                }
            })
            .catch(error => console.error('Error marking as read:', error));
        }
        
        // Helper function to get notification link
        function getNotificationLink(notification) {
            switch (notification.type) {
                case 'vote':
                    return `live_results.php?election=${notification.related_election}`;
                case 'election':
                    return `student.php`;
                case 'result':
                    return `live_results.php?election=${notification.related_election}`;
                case 'candidate':
                    return `student.php`;
                default:
                    return '#';
            }
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Mark initial notifications as read when clicked
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.notificationId;
                if (notificationId) {
                    markNotificationAsRead(notificationId, this);
                }
            });
        });
    });
    </script>

    <!-- Toast CSS -->
    <style>
    .toast {
        transition: all 0.3s ease;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    </style>
</body>
</html>