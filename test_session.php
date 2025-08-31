<?php
// Start session and include necessary files
require_once 'configs/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Session Test Page</h1>
        <div id="sessionInfo" class="mt-4">
            <p>Session ID: <?php echo session_id(); ?></p>
            <p>Last Activity: <span id="lastActivity"><?php echo date('Y-m-d H:i:s', $_SESSION['LAST_ACTIVITY']); ?></span></p>
            <p>Current Time: <span id="currentTime"><?php echo date('Y-m-d H:i:s'); ?></span></p>
            <p>Time Until Timeout: <span id="timeRemaining">10:00</span> minutes</p>
        </div>
        <div class="mt-4">
            <button id="checkSessionBtn" class="btn btn-primary">Check Session</button>
            <button id="forceLogoutBtn" class="btn btn-danger">Force Logout</button>
        </div>
        <div id="console" class="mt-4 p-3 bg-light border rounded" style="height: 200px; overflow-y: auto;">
            <h5>Console Log:</h5>
            <div id="consoleOutput"></div>
        </div>
    </div>

    <!-- Include session-timeout script -->
    <script src="assets/js/session-timeout.js"></script>
    
    <script>
    // Log to console div
    function logToConsole(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        const logEntry = document.createElement('div');
        logEntry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
        consoleOutput.appendChild(logEntry);
        consoleOutput.scrollTop = consoleOutput.scrollHeight;
    }

    // Override console.log to also log to our console div
    const originalConsoleLog = console.log;
    console.log = function(message) {
        originalConsoleLog.apply(console, arguments);
        logToConsole(message);
    };

    // Override console.error to also log to our console div
    const originalConsoleError = console.error;
    console.error = function(message) {
        originalConsoleError.apply(console, arguments);
        logToConsole(`ERROR: ${message}`);
    };

    // Check session status
    document.getElementById('checkSessionBtn').addEventListener('click', async () => {
        try {
            const response = await fetch('api/keepalive.php');
            const data = await response.json();
            logToConsole(`Session check: ${JSON.stringify(data)}`);
            
            // Update the last activity display
            if (data.last_activity) {
                const lastActivity = new Date(data.last_activity * 1000);
                document.getElementById('lastActivity').textContent = lastActivity.toLocaleString();
                
                // Update time remaining
                const timeRemaining = Math.max(0, 600 - Math.floor((Date.now()/1000 - data.last_activity)));
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                document.getElementById('timeRemaining').textContent = 
                    `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        } catch (error) {
            console.error('Error checking session:', error);
        }
    });

    // Force logout
    document.getElementById('forceLogoutBtn').addEventListener('click', () => {
        // Force the session to expire by setting LAST_ACTIVITY to a time long ago
        fetch('api/keepalive.php?force_expire=1')
            .then(response => response.json())
            .then(data => {
                logToConsole('Session force expired. Redirecting to login...');
                setTimeout(() => {
                    window.location.href = 'login.php?session_expired=1';
                }, 1000);
            })
            .catch(error => console.error('Error forcing logout:', error));
    });

    // Update current time every second
    setInterval(() => {
        document.getElementById('currentTime').textContent = new Date().toLocaleString();
    }, 1000);
    </script>
</body>
</html>
