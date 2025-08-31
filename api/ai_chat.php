<?php
// AI Chat API with OpenRouter Integration
// Only handles voting-related questions

// Error reporting and environment setup
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

session_start();

// Load environment variables
require_once '../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    error_log("AI Chat API - Dotenv loading error: " . $e->getMessage());
}

// Include required files
try {
    require_once '../configs/dbconnection.php';
    require_once '../classes/VotingFAQ.php';
    require_once '../classes/OpenRouterAI.php';
    $config = require_once '../configs/ai_chat_config.php';
} catch (Exception $e) {
    error_log("AI Chat API - Include error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    exit;
}

// Set CORS headers based on config
$allowed_origins = $config['allowed_origins'] ?? ['http://localhost'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

// Rate limiting
$rate_limit_enabled = $config['rate_limit']['enable_rate_limiting'] ?? true;
if ($rate_limit_enabled) {
    $max_requests = $config['rate_limit']['max_requests_per_minute'] ?? 20;
    $rate_key = 'ai_chat_requests_' . session_id();
    $current_time = time();
    $requests = $_SESSION[$rate_key] ?? [];
    
    // Clean old requests
    $requests = array_filter($requests, function($timestamp) use ($current_time) {
        return ($current_time - $timestamp) < 60;
    });
    
    if (count($requests) >= $max_requests) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded. Please wait a moment.']);
        exit;
    }
    
    $requests[] = $current_time;
    $_SESSION[$rate_key] = $requests;
}

// Get and validate input
$json_input = file_get_contents('php://input');
if (empty($json_input)) {
    http_response_code(400);
    echo json_encode(['error' => 'No input provided']);
    exit;
}

$input = json_decode($json_input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON format']);
    exit;
}

if (!isset($input['message']) || empty(trim($input['message']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$userMessage = trim($input['message']);

// Validate message length
$max_length = $config['message']['max_length'] ?? 500;
$min_length = $config['message']['min_length'] ?? 3;

if (strlen($userMessage) > $max_length) {
    http_response_code(400);
    echo json_encode(['error' => "Message too long. Maximum $max_length characters allowed."]);
    exit;
}

if (strlen($userMessage) < $min_length) {
    http_response_code(400);
    echo json_encode(['error' => "Message too short. Minimum $min_length characters required."]);
    exit;
}

// Sanitize input
if (!($config['message']['allow_html'] ?? false)) {
    $userMessage = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
}

// Get user info
$isLoggedIn = isset($_SESSION['login_id']) && !empty($_SESSION['login_id']);
$studentId = $isLoggedIn ? (int)$_SESSION['login_id'] : null;
$userRole = $_SESSION['role'] ?? 'guest';

// Logging setup
$enable_logging = $config['logging']['enable_logging'] ?? false;
$log_file = $config['logging']['log_file'] ?? '../logs/ai_chat.log';

// Log request
if ($enable_logging) {
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'request',
        'session_id' => session_id(),
        'user_id' => $studentId,
        'user_role' => $userRole,
        'message' => $userMessage,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

try {
    // Check if message is voting-related first
    if (!VotingFAQKnowledgeBase::isVotingRelated($userMessage)) {
        $response = [
            'response' => "Sorry, I can only help with voting-related questions. Please ask me about the voting process, candidates, election schedule, or any other election-related topics.",
            'type' => 'restriction',
            'source' => 'validation',
            'suggestions' => [
                "How do I vote?",
                "When does voting end?",
                "Is my vote secure?",
                "Who are the candidates?",
                "What if I have problems voting?"
            ]
        ];
        
        echo json_encode($response);
        exit;
    }
    
    $aiResponse = null;
    $responseSource = 'local';
    $responseData = [];
    
    // Try OpenRouter first if enabled
    $useOpenRouter = $config['ai']['use_openrouter'] ?? false;
    if ($useOpenRouter) {
        try {
            $openrouter = new OpenRouterAI($config);
            
            if ($openrouter->isEnabled()) {
                // Get conversation history
                $conversationHistory = $_SESSION['ai_chat_history'] ?? [];
                
                $aiResponse = $openrouter->generateResponse($userMessage, $conversationHistory);
                $responseSource = 'openrouter';
                $responseData['model'] = $openrouter->getCurrentModel();
                
                // Store conversation history
                if (!isset($_SESSION['ai_chat_history'])) {
                    $_SESSION['ai_chat_history'] = [];
                }
                
                $_SESSION['ai_chat_history'][] = [
                    'user' => $userMessage,
                    'assistant' => $aiResponse,
                    'timestamp' => time()
                ];
                
                // Keep only recent history
                $maxHistory = $config['ai']['max_context_history'] ?? 3;
                if (count($_SESSION['ai_chat_history']) > $maxHistory) {
                    $_SESSION['ai_chat_history'] = array_slice($_SESSION['ai_chat_history'], -$maxHistory);
                }
            }
        } catch (Exception $e) {
            // Log OpenRouter error
            if ($enable_logging) {
                $error_log = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'type' => 'error',
                    'source' => 'openrouter',
                    'error' => $e->getMessage(),
                    'fallback' => $config['openrouter']['fallback_to_local'] ?? true
                ];
                file_put_contents($log_file, json_encode($error_log) . "\n", FILE_APPEND | LOCK_EX);
            }
            
            // Use fallback if enabled
            if (!($config['openrouter']['fallback_to_local'] ?? true)) {
                throw $e;
            }
            
            $responseSource = 'local_fallback';
        }
    }
    
    // Use local knowledge base if OpenRouter failed or is disabled
    if ($aiResponse === null) {
        $matches = VotingFAQKnowledgeBase::searchFAQ($userMessage);
        
        if (!empty($matches)) {
            $bestMatch = $matches[0];
            $aiResponse = $bestMatch['answer'];
            $responseData['confidence'] = min(100, $bestMatch['score'] * 20);
            $responseData['related_question'] = $bestMatch['question'];
            $responseData['type'] = 'faq';
        } else {
            // Default response
            $aiResponse = "I understand you have a question about voting. Here are some things I can help with:\n\n" .
                         "• How to cast your vote\n" .
                         "• Information about candidates\n" .
                         "• Election schedule and deadlines\n" .
                         "• Vote security and privacy\n" .
                         "• Technical voting issues\n\n" .
                         "Please ask a more specific question about any of these topics!";
            
            if ($isLoggedIn && $userRole === 'student') {
                $aiResponse .= "\n\n💡 As a logged-in student, you can vote in the current election. Would you like help with the voting process?";
            }
            
            $responseData['type'] = 'general_help';
        }
    } else {
        $responseData['type'] = 'ai_generated';
    }
    
    // Build final response
    $response = array_merge([
        'response' => $aiResponse,
        'source' => $responseSource,
        'suggestions' => getContextualSuggestions($userMessage)
    ], $responseData);
    
    // Log successful response
    if ($enable_logging) {
        $response_log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'response',
            'session_id' => session_id(),
            'response_type' => $response['type'],
            'response_source' => $responseSource,
            'response_length' => strlen($aiResponse)
        ];
        file_put_contents($log_file, json_encode($response_log) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log error
    error_log("AI Chat API Error: " . $e->getMessage());
    
    if ($enable_logging) {
        $error_log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'error',
            'session_id' => session_id(),
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
        file_put_contents($log_file, json_encode($error_log) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    http_response_code(500);
    echo json_encode([
        'response' => "I'm sorry, I'm experiencing technical difficulties. Please try asking your question again in a moment.",
        'type' => 'error',
        'source' => 'system'
    ]);
}

// Helper function for contextual suggestions
function getContextualSuggestions($message) {
    $message = strtolower($message);
    
    if (strpos($message, 'how') !== false || strpos($message, 'vote') !== false) {
        return [
            "What are the voting requirements?",
            "How do I view candidate manifestos?",
            "Is my vote anonymous?",
            "Can I vote on mobile?"
        ];
    }
    
    if (strpos($message, 'secure') !== false || strpos($message, 'safe') !== false) {
        return [
            "How is blockchain used?",
            "Can anyone see my vote?",
            "What if someone hacks the system?",
            "How do I know my vote counted?"
        ];
    }
    
    if (strpos($message, 'candidate') !== false) {
        return [
            "How do I read manifestos?",
            "How many candidates are there?",
            "What positions are available?",
            "Can candidates see votes?"
        ];
    }
    
    if (strpos($message, 'time') !== false || strpos($message, 'when') !== false) {
        return [
            "How much time is left?",
            "What happens after voting ends?",
            "Can I vote early?",
            "What if I miss the deadline?"
        ];
    }
    
    return [
        "How do I vote?",
        "Is voting secure?",
        "When does voting end?",
        "Who are the candidates?"
    ];
}
?>
