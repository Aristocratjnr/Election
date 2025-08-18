<?php
// Set session configuration
if (session_status() === PHP_SESSION_NONE) {
    // These settings must be set before session_start()
    ini_set('session.cookie_lifetime', 0); // Until browser is closed
    ini_set('session.gc_maxlifetime', 600); // 10 minutes (600 seconds)
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    
    // Start the session
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("location:login.php");
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Log out if session has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
    // Last request was more than 10 minutes ago
    session_unset();
    session_destroy();
    header("location:login.php?session_expired=1");
    exit();
}