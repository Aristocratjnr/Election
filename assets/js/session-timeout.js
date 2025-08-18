// Session Timeout Configuration
console.log('Session timeout script loaded - Student Portal');
const SESSION_TIMEOUT = 10 * 60 * 1000; // 10 minutes (in milliseconds)
const WARNING_TIME = 2 * 60 * 1000; // 2 minutes before timeout
const KEEPALIVE_INTERVAL = 1 * 60 * 1000; // Ping every 1 minute

console.log(`Session timeout configured: ${SESSION_TIMEOUT/60000} minutes total, ${WARNING_TIME/60000} minutes warning, pinging every ${KEEPALIVE_INTERVAL/60000} minutes`);

// Debug function to log with timestamp
function logDebug(message) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ${message}`);
}

// Check if Bootstrap is available
if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap is not loaded. Session timeout modal will not work.');
}

let warningTimer;
let timeoutTimer;

// Function to reset timers on user activity
function resetTimers() {
    logDebug('Resetting timers due to user activity');
    
    // Clear existing timers
    if (warningTimer) clearTimeout(warningTimer);
    if (timeoutTimer) clearTimeout(timeoutTimer);
    
    // Calculate time until warning and timeout
    const timeUntilWarning = SESSION_TIMEOUT - WARNING_TIME;
    const timeUntilLogout = SESSION_TIMEOUT;
    
    logDebug(`Setting timers - Warning in ${timeUntilWarning/1000}s, Logout in ${timeUntilLogout/1000}s`);
    
    // Set warning timer
    warningTimer = setTimeout(showTimeoutWarning, timeUntilWarning);
    
    // Set actual timeout
    timeoutTimer = setTimeout(logoutUser, timeUntilLogout);
    
    // Send immediate keepalive on user activity
    sendKeepalive();
}

// Function to send keepalive request
function sendKeepalive() {
    logDebug('Sending keepalive ping');
    
    fetch('api/keepalive.php', {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        credentials: 'same-origin' // Include cookies
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        logDebug(`Keepalive response: ${JSON.stringify(data)}`);
        if (data.status === 'success') {
            logDebug(`Session extended. New activity time: ${new Date(data.last_activity * 1000).toISOString()}`);
        } else {
            console.error('Keepalive failed:', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Keepalive error:', error);
    });
}

// Function to show warning modal
function showTimeoutWarning() {
    logDebug('Showing session timeout warning');
    
    // Check if we're on the login page
    if (window.location.pathname.endsWith('login.php')) {
        logDebug('On login page, skipping timeout warning');
        return;
    }
    // Create modal if it doesn't exist
    if (!document.getElementById('sessionTimeoutModal')) {
        const modalHTML = `
            <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-labelledby="sessionTimeoutModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="sessionTimeoutModalLabel">Session Timeout Warning</h5>
                        </div>
                        <div class="modal-body">
                            <p>Your session will expire in <span id="countdown">5:00</span> minutes due to inactivity.</p>
                            <p>Click "Continue" to stay logged in.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="continueSession">Continue</button>
                            <button type="button" class="btn btn-secondary" id="logoutNow">Logout Now</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Initialize modal
        const modal = new bootstrap.Modal(document.getElementById('sessionTimeoutModal'));
        modal.show();
        
        // Start countdown
        let secondsLeft = WARNING_TIME / 1000;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            secondsLeft--;
            const minutes = Math.floor(secondsLeft / 60);
            const seconds = secondsLeft % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
        
        // Event listeners for modal buttons
        document.getElementById('continueSession').addEventListener('click', () => {
            clearInterval(countdownInterval);
            resetTimers();
            modal.hide();
            // Ping server to keep session alive
            fetch('api/keepalive.php').catch(() => {});
        });
        
        document.getElementById('logoutNow').addEventListener('click', () => {
            clearInterval(countdownInterval);
            window.location.href = 'logout.php';
        });
        
        // Close modal when clicking outside
        document.getElementById('sessionTimeoutModal').addEventListener('hidden.bs.modal', () => {
            clearInterval(countdownInterval);
            resetTimers();
        });
    }
}

// Function to handle user logout
function logoutUser() {
    // Redirect to logout page or show login page
    window.location.href = 'logout.php?timeout=1';
}

// Initialize timers when the page loads
document.addEventListener('DOMContentLoaded', function() {
    logDebug('DOM fully loaded, initializing session timeout');
    
    // Set up user activity listeners
    const activityEvents = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, resetTimers, { passive: true });
    });
    
    // Initial reset of timers
    resetTimers();
    
    // Set up periodic keepalive pings
    setInterval(sendKeepalive, KEEPALIVE_INTERVAL);
    
    logDebug('Session timeout system initialized');
});
