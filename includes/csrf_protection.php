<?php
/**
 * CSRF Protection Functions
 * 
 * These functions provide protection against Cross-Site Request Forgery attacks
 * by generating and validating CSRF tokens.
 */

/**
 * Generate a new CSRF token and store it in the session
 * 
 * @return string The generated CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    // Generate a cryptographically secure random token
    $token = bin2hex(random_bytes(32));
    
    // Store the token with timestamp for expiration checks
    $_SESSION['csrf_tokens'][$token] = time();
    
    // Clean up old tokens (tokens older than 2 hours)
    $expireTime = time() - 7200; // 2 hours
    foreach ($_SESSION['csrf_tokens'] as $t => $time) {
        if ($time < $expireTime) {
            unset($_SESSION['csrf_tokens'][$t]);
        }
    }
    
    return $token;
}

/**
 * Create a hidden input field with the CSRF token
 * 
 * @return string HTML for the hidden CSRF token input
 */
function generateCSRFTokenField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate a submitted CSRF token
 * 
 * @param string $token The token to validate
 * @return bool True if the token is valid, false otherwise
 */
function validateCSRFToken($token) {
    // If no token provided or tokens array doesn't exist, validation fails
    if (empty($token) || !isset($_SESSION['csrf_tokens'])) {
        return false;
    }
    
    // Check if the token exists in the session
    if (isset($_SESSION['csrf_tokens'][$token])) {
        // Token is valid, remove it from the session to prevent reuse
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }
    
    return false;
}

/**
 * Verify CSRF token and terminate execution if invalid
 * 
 * @param string $token The token to verify
 * @param string $redirectUrl URL to redirect to if token is invalid
 * @return void
 */
function verifyCSRFToken($token, $redirectUrl = null) {
    if (!validateCSRFToken($token)) {
        // Invalid token, terminate execution
        http_response_code(403);
        
        if ($redirectUrl) {
            // Redirect with error message
            header("Location: $redirectUrl?error=invalid_csrf");
            exit;
        } else {
            // Display error and terminate
            die("Invalid security token. Please try again.");
        }
    }
}
?>
