<?php
// Keep session alive endpoint
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = [
    'status' => 'success',
    'timestamp' => time(),
    'session_id' => session_id(),
    'session_status' => session_status(),
    'session_active' => isset($_SESSION['login_id'])
];

// Handle force expire request
if (isset($_GET['force_expire'])) {
    // Set last activity to a time long ago to force expiration
    $_SESSION['LAST_ACTIVITY'] = time() - 3600; // 1 hour ago
    $response['message'] = 'Session expiration forced';
    $response['last_activity'] = $_SESSION['LAST_ACTIVITY'];
    error_log('Force expiring session: ' . session_id());
} else {
    // Normal keepalive request
    if (isset($_SESSION['login_id'])) {
        // Update last activity time
        $_SESSION['LAST_ACTIVITY'] = time();
        $response['last_activity'] = $_SESSION['LAST_ACTIVITY'];
        error_log('Session ' . session_id() . ' activity updated to: ' . date('Y-m-d H:i:s', $_SESSION['LAST_ACTIVITY']));
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No active session';
        http_response_code(401);
    }
}

// Add debug info in development
$response['debug'] = [
    'session_cookie_params' => session_get_cookie_params(),
    'session_vars' => array_keys($_SESSION)
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
