/**
 * Notifications handling for the Election Management System
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notifications audio
    const notificationSound = new Audio('assets/audio/sounds/notification.mp3');
    
    // Initialize notifications
    initializeNotifications();
    
    // Setup refresh button
    const refreshBtn = document.getElementById('refresh-notifications');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            loadNotifications(true);
        });
    }
    
    // Setup notification dropdown toggle
    const notificationDropdown = document.getElementById('notification-dropdown');
    if (notificationDropdown) {
        notificationDropdown.addEventListener('show.bs.dropdown', function() {
            loadNotifications();
        });
    }
    
    // Store this in window to be accessible everywhere
    window.playNotificationSound = function() {
        notificationSound.currentTime = 0; // Reset sound to start
        notificationSound.play().catch(err => {
            console.log('Audio playback was prevented:', err);
        });
    };
});

/**
 * Initialize notifications
 */
function initializeNotifications() {
    // Track the previous notification count to detect new ones
    window.previousNotificationCount = 0;
    
    // Check for unread notifications immediately
    checkUnreadNotifications();
    
    // Set interval to check for new notifications every minute
    setInterval(checkUnreadNotifications, 60000);
}

/**
 * Check for unread notifications
 */
function checkUnreadNotifications() {
    const studentID = getUserID();
    if (!studentID) return;
    
    fetch(`api/notifications_count.php?user_id=${studentID}&user_type=student`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const currentCount = data.count || 0;
                const previousCount = window.previousNotificationCount || 0;
                
                // Only play sound if there are new notifications (count increased)
                if (currentCount > previousCount && currentCount > 0) {
                    if (window.playNotificationSound) {
                        window.playNotificationSound();
                    }
                }
                
                // Store the new count for next comparison
                window.previousNotificationCount = currentCount;
                
                // Update notification badge
                if (currentCount > 0) {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        badge.textContent = currentCount;
                        badge.classList.remove('d-none');
                        
                        // Update notification counter in dropdown
                        const counter = document.getElementById('notification-counter');
                        if (counter) {
                            counter.textContent = currentCount + ' new';
                            counter.style.display = 'inline-block';
                        }
                    }
                } else {
                    // Hide badge if no notifications
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        badge.classList.add('d-none');
                    }
                }
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

/**
 * Load notifications into the dropdown
 * @param {boolean} forceRefresh - Force refresh even if already loaded
 */
function loadNotifications(forceRefresh = false) {
    const notificationList = document.getElementById('notification-list');
    const loadingIndicator = document.getElementById('notification-loading');
    const emptyState = document.getElementById('notification-empty');
    
    if (!notificationList) return;
    
    const studentID = getUserID();
    if (!studentID) {
        showEmptyState(emptyState, 'Please log in to view notifications');
        return;
    }
    
    // Always clear and show loading indicator when forcing refresh
    if (forceRefresh) {
        notificationList.innerHTML = '';
        notificationList.dataset.loaded = 'false';
    }
    
    // Only load once unless forced
    if (!forceRefresh && notificationList.dataset.loaded === 'true') return;
    
    // Show loading
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    if (emptyState) emptyState.style.display = 'none';
    
    // Add timestamp to prevent caching
    const timestamp = new Date().getTime();
    
    fetch(`api/notifications.php?user_id=${studentID}&user_type=student&t=${timestamp}`)
        .then(response => response.json())
        .then(data => {
            // Hide loading
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            
            // Handle empty notifications
            if (!data.success || !data.notifications || data.notifications.length === 0) {
                showEmptyState(emptyState);
                return;
            }
            
            // Render notifications
            notificationList.innerHTML = '';
            data.notifications.forEach(notification => {
                notificationList.appendChild(createNotificationItem(notification));
            });
            
            // Show load more button if needed
            if (data.has_more) {
                const loadMoreItem = document.createElement('div');
                loadMoreItem.className = 'dropdown-item text-center py-2';
                loadMoreItem.innerHTML = '<a href="notifications.php" class="text-decoration-none">View all notifications</a>';
                notificationList.appendChild(loadMoreItem);
            }
            
            // Mark as loaded
            notificationList.dataset.loaded = 'true';
            
            // Also update the badge count
            if (data.unread !== undefined) {
                updateBadgeCount(data.unread);
            }
            
            // Reset unread counter in dropdown if we've shown them
            const counter = document.getElementById('notification-counter');
            if (counter) {
                counter.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            showEmptyState(emptyState, 'Error loading notifications');
        });
}

/**
 * Update the notification badge count
 * @param {number} count - Number of unread notifications
 */
function updateBadgeCount(count) {
    const badge = document.getElementById('notification-badge');
    if (!badge) return;
    
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

/**
 * Create a notification item element
 * @param {Object} notification - Notification data
 * @returns {HTMLElement} - Notification element
 */
function createNotificationItem(notification) {
    const item = document.createElement('a');
    item.href = getNotificationLink(notification);
    item.className = 'dropdown-item notification-item d-flex align-items-center py-3';
    item.dataset.id = notification.notification_id;
    
    if (notification.is_read == 0) {
        item.classList.add('unread');
    }
    
    const iconClass = notification.icon || 'bi-bell';
    const bgClass = notification.bg_class || 'bg-primary-light';
    
    item.innerHTML = `
        <div class="flex-shrink-0 me-3">
            <div class="${bgClass} p-2 rounded">
                <i class="bi ${iconClass} text-primary"></i>
            </div>
        </div>
        <div class="flex-grow-1">
            <h6 class="mb-0 fw-semibold">${escapeHTML(notification.title)}</h6>
            <p class="mb-0 text-muted small">${escapeHTML(notification.message)}</p>
            <div class="mt-1">
                <small class="text-muted">${notification.time_ago}</small>
            </div>
        </div>
    `;
    
    // Add click handler to mark as read
    item.addEventListener('click', function(e) {
        markNotificationAsRead(notification.notification_id);
        if (notification.is_read == 0) {
            // Update badge count
            const badge = document.getElementById('notification-badge');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                if (count > 0) {
                    badge.textContent = count;
                } else {
                    badge.classList.add('d-none');
                }
            }
        }
    });
    
    return item;
}

/**
 * Show empty state message
 * @param {HTMLElement} element - Empty state element
 * @param {string} message - Optional custom message
 */
function showEmptyState(element, message = null) {
    if (!element) return;
    
    element.style.display = 'block';
    if (message) {
        const messageEl = element.querySelector('.empty-message');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }
}

/**
 * Get appropriate link for a notification
 * @param {Object} notification - Notification data
 * @returns {string} - URL to navigate to
 */
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

/**
 * Mark a notification as read
 * @param {number} notificationId - Notification ID
 */
function markNotificationAsRead(notificationId) {
    fetch(`api/mark_notification_read.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .catch(error => console.error('Error marking notification as read:', error));
}

/**
 * Get the current user ID
 * @returns {number|null} - User ID or null if not logged in
 */
function getUserID() {
    // Try to get from data attribute
    const userIdElement = document.querySelector('[data-user-id]');
    if (userIdElement && userIdElement.dataset.userId) {
        return parseInt(userIdElement.dataset.userId);
    }
    
    // Try to get from hidden input
    const userIdInput = document.getElementById('current-user-id');
    if (userIdInput && userIdInput.value) {
        return parseInt(userIdInput.value);
    }
    
    return null;
}

/**
 * Escape HTML special characters
 * @param {string} text - Text to escape
 * @returns {string} - Escaped text
 */
function escapeHTML(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}