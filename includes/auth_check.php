<?php
// Enable strict error reporting
declare(strict_types=1);

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 day
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true
    ]);
}

/**
 * Redirect to login page with message
 */
function redirect_to_login(string $message = ''): void {
    $_SESSION['login_redirect_message'] = $message;
    header('Location: login.php');
    exit();
}

/**
 * Check if user is authenticated
 */
function is_authenticated(): bool {
    return isset($_SESSION['login_id']) && !empty($_SESSION['login_id']);
}

/**
 * Check if user has admin role
 */
function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Main authentication check
 */
function require_admin_auth(): void {
    // Check if user is logged in
    if (!is_authenticated()) {
        redirect_to_login('Please login to access this page');
    }

    // Check if user is admin
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied. Administrator privileges required.');
    }

    // Check session regeneration for security
    if (!isset($_SESSION['auth_last_check'])) {
        session_regenerate_id(true);
        $_SESSION['auth_last_check'] = time();
    } elseif (time() - $_SESSION['auth_last_check'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['auth_last_check'] = time();
    }

    // Check for IP changes (basic security)
    if (isset($_SESSION['auth_ip']) && $_SESSION['auth_ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        redirect_to_login('Session security violation detected');
    }
}

// Run the check on pages that include this file
require_admin_auth();
?>