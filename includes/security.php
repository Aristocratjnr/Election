<?php
/**
 * Security Functions for Election System
 * 
 * This file contains security-related functions that can be used across the application
 * to enhance protection against common web vulnerabilities.
 */

/**
 * Set security headers to protect against common attacks
 */
function setSecurityHeaders() {
    // Protect against XSS attacks
    header("X-XSS-Protection: 1; mode=block");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Prevent clickjacking
    header("X-Frame-Options: SAMEORIGIN");
    
    // Control referrer information
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Basic Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data:;");
}

/**
 * Sanitize user input
 * 
 * @param string $input User input to sanitize
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate that an input is a positive integer
 * 
 * @param mixed $input Input to validate
 * @return int|false The validated integer or false if invalid
 */
function validatePositiveInt($input) {
    $input = filter_var($input, FILTER_VALIDATE_INT);
    return ($input !== false && $input > 0) ? $input : false;
}

/**
 * Log security events
 * 
 * @param string $message Log message
 * @param string $level Log level (info, warning, error)
 * @param array $context Additional context data
 */
function logSecurityEvent($message, $level = 'info', $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['login_id'] ?? 'unauthenticated';
    
    $logEntry = "[{$timestamp}] [{$level}] [{$ip}] [User: {$user}] {$message}";
    
    if (!empty($context)) {
        $logEntry .= " Context: " . json_encode($context);
    }
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }
    
    $logFile = $logDir . '/security.log';
    error_log($logEntry . "\n", 3, $logFile);
}

/**
 * Check for suspicious activity in user requests
 * 
 * @return bool True if suspicious activity detected
 */
function detectSuspiciousActivity() {
    $suspicious = false;
    $context = [];
    
    // Check for SQL injection attempts
    $sqlPatterns = [
        '/(\%27)|(\')|(\-\-)|(\%23)|(#)/',
        '/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/',
        '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/',
        '/((\%27)|(\'))union/',
        '/exec(\s|\+)+(s|x)p\w+/'
    ];
    
    foreach ($_GET as $key => $value) {
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $suspicious = true;
                $context['type'] = 'sql_injection';
                $context['param'] = $key;
                $context['value'] = $value;
                break 2;
            }
        }
    }
    
    // Check for XSS attempts
    $xssPatterns = [
        '/<script\b[^>]*>(.*?)<\/script>/i',
        '/javascript\s*:/i',
        '/onclick\s*=/i',
        '/onerror\s*=/i',
        '/onload\s*=/i'
    ];
    
    foreach ($_GET as $key => $value) {
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $suspicious = true;
                $context['type'] = 'xss';
                $context['param'] = $key;
                $context['value'] = $value;
                break 2;
            }
        }
    }
    
    if ($suspicious) {
        logSecurityEvent("Suspicious activity detected", "warning", $context);
    }
    
    return $suspicious;
}

/**
 * Generate a secure random token
 * 
 * @param int $length Length of the token
 * @return string The generated token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash sensitive data
 * 
 * @param string $data Data to hash
 * @return string Hashed data
 */
function hashSensitiveData($data) {
    return hash('sha256', $data);
}
?>
