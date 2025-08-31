<?php
/**
 * AI Chat Health Check Endpoint
 * Provides system status and configuration validation
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Load configuration
    $config = require_once '../configs/ai_chat_config.php';
    
    // Check system requirements
    $health_status = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => $config['environment'] ?? 'unknown',
        'checks' => []
    ];
    
    // Check PHP version
    $health_status['checks']['php_version'] = [
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail',
        'value' => PHP_VERSION,
        'required' => '7.4.0+'
    ];
    
    // Check if required directories exist and are writable
    $log_dir = dirname($config['logging']['log_file'] ?? '../logs/ai_chat.log');
    $health_status['checks']['log_directory'] = [
        'status' => (is_dir($log_dir) && is_writable($log_dir)) ? 'pass' : 'fail',
        'path' => $log_dir,
        'writable' => is_writable($log_dir)
    ];
    
    // Check if VotingFAQ class is available
    $health_status['checks']['voting_faq_class'] = [
        'status' => class_exists('VotingFAQKnowledgeBase') ? 'pass' : 'fail',
        'available' => class_exists('VotingFAQKnowledgeBase')
    ];
    
    // Check database connection (if needed)
    try {
        include_once '../configs/dbconnection.php';
        $health_status['checks']['database'] = [
            'status' => isset($conn) && $conn->ping() ? 'pass' : 'fail',
            'connected' => isset($conn) && $conn->ping()
        ];
    } catch (Exception $e) {
        $health_status['checks']['database'] = [
            'status' => 'fail',
            'error' => $e->getMessage()
        ];
    }
    
    // Check session functionality
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $health_status['checks']['sessions'] = [
        'status' => (session_status() === PHP_SESSION_ACTIVE) ? 'pass' : 'fail',
        'session_id' => session_id()
    ];
    
    // Check configuration completeness
    $required_config_keys = [
        'allowed_origins',
        'rate_limit',
        'message',
        'response',
        'logging'
    ];
    
    $missing_config = [];
    foreach ($required_config_keys as $key) {
        if (!isset($config[$key])) {
            $missing_config[] = $key;
        }
    }
    
    $health_status['checks']['configuration'] = [
        'status' => empty($missing_config) ? 'pass' : 'fail',
        'missing_keys' => $missing_config
    ];
    
    // Overall health determination
    $all_checks_passed = true;
    foreach ($health_status['checks'] as $check) {
        if ($check['status'] !== 'pass') {
            $all_checks_passed = false;
            break;
        }
    }
    
    $health_status['status'] = $all_checks_passed ? 'healthy' : 'unhealthy';
    
    // Add system info
    $health_status['system_info'] = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize')
    ];
    
    // Return appropriate HTTP status
    if (!$all_checks_passed) {
        http_response_code(503); // Service Unavailable
    }
    
    echo json_encode($health_status, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage()
    ]);
}
?>
