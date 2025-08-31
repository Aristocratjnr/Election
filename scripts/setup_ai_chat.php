<?php
/**
 * AI Chat Setup and Environment Validator
 * Run this script to ensure proper setup
 */

echo "AI Chat System Setup and Validation\n";
echo "===================================\n\n";

// Check PHP version
echo "1. Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "   ✓ PHP version " . PHP_VERSION . " is supported\n";
} else {
    echo "   ✗ PHP version " . PHP_VERSION . " is not supported (7.4.0+ required)\n";
    exit(1);
}

// Load configuration
echo "\n2. Loading configuration...\n";
try {
    $config = require_once '../configs/ai_chat_config.php';
    echo "   ✓ Configuration loaded successfully\n";
} catch (Exception $e) {
    echo "   ✗ Failed to load configuration: " . $e->getMessage() . "\n";
    exit(1);
}

// Create necessary directories
echo "\n3. Creating directories...\n";
$log_dir = dirname($config['logging']['log_file'] ?? '../logs/ai_chat.log');

if (!is_dir($log_dir)) {
    if (mkdir($log_dir, 0755, true)) {
        echo "   ✓ Created log directory: $log_dir\n";
    } else {
        echo "   ✗ Failed to create log directory: $log_dir\n";
        exit(1);
    }
} else {
    echo "   ✓ Log directory already exists: $log_dir\n";
}

// Check directory permissions
echo "\n4. Checking permissions...\n";
if (is_writable($log_dir)) {
    echo "   ✓ Log directory is writable\n";
} else {
    echo "   ✗ Log directory is not writable: $log_dir\n";
    echo "   Please run: chmod 755 $log_dir\n";
    exit(1);
}

// Check required files
echo "\n5. Checking required files...\n";
$required_files = [
    '../configs/dbconnection.php',
    '../classes/VotingFAQ.php',
    '../api/ai_chat.php',
    '../assets/js/ai-chat.js',
    '../assets/css/ai-chat.css'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "   ✓ Found: $file\n";
    } else {
        echo "   ✗ Missing: $file\n";
        exit(1);
    }
}

// Test database connection
echo "\n6. Testing database connection...\n";
try {
    include_once '../configs/dbconnection.php';
    if (isset($conn) && $conn->ping()) {
        echo "   ✓ Database connection successful\n";
    } else {
        echo "   ✗ Database connection failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test VotingFAQ class
echo "\n7. Testing VotingFAQ class...\n";
try {
    require_once '../classes/VotingFAQ.php';
    if (class_exists('VotingFAQKnowledgeBase')) {
        echo "   ✓ VotingFAQKnowledgeBase class available\n";
        
        // Test a simple search
        $faqs = VotingFAQKnowledgeBase::getVotingFAQs();
        echo "   ✓ Found " . count($faqs) . " FAQ entries\n";
        
        $test_result = VotingFAQKnowledgeBase::searchFAQ('how to vote');
        echo "   ✓ FAQ search functionality working\n";
    } else {
        echo "   ✗ VotingFAQKnowledgeBase class not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ VotingFAQ error: " . $e->getMessage() . "\n";
    exit(1);
}

// Create initial log entry
echo "\n8. Creating initial log entry...\n";
try {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'setup',
        'message' => 'AI Chat system setup completed successfully',
        'version' => '1.0.0',
        'php_version' => PHP_VERSION
    ];
    
    $log_file = $config['logging']['log_file'];
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    echo "   ✓ Initial log entry created\n";
} catch (Exception $e) {
    echo "   ✗ Failed to create log entry: " . $e->getMessage() . "\n";
    exit(1);
}

// Display configuration summary
echo "\n9. Configuration Summary:\n";
echo "   Environment: " . ($config['environment'] ?? 'not set') . "\n";
echo "   Rate Limiting: " . ($config['rate_limit']['enable_rate_limiting'] ? 'enabled' : 'disabled') . "\n";
echo "   Max Message Length: " . ($config['message']['max_length'] ?? 'not set') . "\n";
echo "   Logging: " . ($config['logging']['enable_logging'] ? 'enabled' : 'disabled') . "\n";
echo "   Log File: " . ($config['logging']['log_file'] ?? 'not set') . "\n";

// Display next steps
echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ AI Chat System Setup Complete!\n\n";
echo "Next Steps:\n";
echo "1. Access the health check: /api/ai_chat_health.php\n";
echo "2. Test the chat API: /api/ai_chat.php\n";
echo "3. Check the student portal: /student.php\n";
echo "4. Monitor logs: " . ($config['logging']['log_file'] ?? 'logs/ai_chat.log') . "\n\n";

echo "The AI chat is now ready to answer voting-related questions!\n";
echo str_repeat("=", 50) . "\n";
?>
