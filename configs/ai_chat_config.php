<?php
/**
 * AI Chat Configuration with dotenv support
 * Environment variables are loaded from .env file
 */

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // Fallback to default values if .env file is not found
    error_log("Warning: Could not load .env file for AI Chat config: " . $e->getMessage());
}

// Helper function to get environment variable with default
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

return [
    // Environment settings
    'environment' => env('APP_ENV', 'development'),
    
    // Security settings
    'allowed_origins' => array_filter(explode(',', env('AI_CHAT_ALLOWED_ORIGINS', 'http://localhost'))),
    
    // Rate limiting
    'rate_limit' => [
        'max_requests_per_minute' => (int)env('AI_CHAT_RATE_LIMIT', 20),
        'max_requests_per_hour' => (int)env('AI_CHAT_RATE_LIMIT', 20) * 10,
        'enable_rate_limiting' => filter_var(env('AI_CHAT_RATE_LIMIT_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN)
    ],
    
    // Message settings
    'message' => [
        'max_length' => (int)env('AI_CHAT_MAX_MESSAGE_LENGTH', 500),
        'min_length' => (int)env('AI_CHAT_MIN_MESSAGE_LENGTH', 3),
        'allow_html' => filter_var(env('AI_CHAT_ALLOW_HTML', 'false'), FILTER_VALIDATE_BOOLEAN),
        'profanity_filter' => true
    ],
    
    // Response settings
    'response' => [
        'delay_min_ms' => 800,
        'delay_max_ms' => 2000,
        'max_length' => 2000,
        'enable_artificial_delay' => true
    ],
    
    // Logging settings
    'logging' => [
        'enable_logging' => filter_var(env('AI_CHAT_ENABLE_LOGGING', 'true'), FILTER_VALIDATE_BOOLEAN),
        'log_file' => '../logs/ai_chat.log',
        'log_level' => env('AI_CHAT_DEBUG', 'false') === 'true' ? 'debug' : 'info',
        'log_requests' => true,
        'log_responses' => true,
        'log_errors' => true,
        'max_log_file_size' => 10485760, // 10MB
        'log_retention_days' => 30
    ],
    
    // OpenRouter API settings
    'openrouter' => [
        'enabled' => filter_var(env('AI_USE_OPENROUTER', 'true'), FILTER_VALIDATE_BOOLEAN),
        'api_key' => env('OPENROUTER_API_KEY', ''),
        'base_url' => 'https://openrouter.ai/api/v1/chat/completions',
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3-haiku'),
        'alternative_models' => [
            'openai/gpt-3.5-turbo',
            'meta-llama/llama-3-8b-instruct',
            'anthropic/claude-3-sonnet',
            'google/gemma-7b-it'
        ],
        'max_tokens' => (int)env('OPENROUTER_MAX_TOKENS', 300),
        'temperature' => (float)env('OPENROUTER_TEMPERATURE', 0.3),
        'timeout' => 15,
        'fallback_to_local' => filter_var(env('AI_FALLBACK_TO_LOCAL', 'true'), FILTER_VALIDATE_BOOLEAN),
        'retry_attempts' => 2,
        'system_prompt' => "You are a helpful voting assistant for a student election system. You ONLY answer questions related to voting, elections, candidates, voting process, election security, deadlines, and technical voting issues. For ANY non-voting questions, you MUST respond exactly with: 'Sorry, I can only help with voting-related questions.' Keep responses under 250 words, helpful, and professional. Focus on actionable voting guidance.",
        'voting_context' => "This is a student election portal where students vote for leadership positions using secure blockchain technology.",
        'app_name' => 'ElectionVotingAssistant',
        'site_url' => env('BASE_URL', 'http://localhost/Election')
    ],
    
    // AI Chat settings
    'ai' => [
        'personality' => 'helpful',
        'response_style' => 'detailed',
        'enable_context_memory' => true,
        'max_context_history' => (int)env('AI_MAX_CONTEXT_HISTORY', 3),
        'enable_personalization' => true,
        'enable_suggestions' => true,
        'use_openrouter' => filter_var(env('AI_USE_OPENROUTER', 'true'), FILTER_VALIDATE_BOOLEAN),
        'openrouter_priority' => true
    ],
    
    // Security features
    'security' => [
        'enable_csrf_protection' => true,
        'enable_input_sanitization' => true,
        'enable_output_encoding' => true,
        'max_session_duration' => 3600,
        'enable_ip_whitelisting' => false,
        'allowed_ips' => []
    ],
    
    // Feature flags
    'features' => [
        'enable_api_endpoint' => true,
        'enable_advanced_nlp' => false,
        'enable_sentiment_analysis' => false,
        'enable_conversation_export' => false,
        'enable_analytics' => true
    ],
    
    // Database settings
    'database' => [
        'enable_chat_history' => false,
        'history_retention_days' => 7,
        'enable_analytics_storage' => true
    ],
    
    // Performance settings
    'performance' => [
        'enable_caching' => true,
        'cache_duration' => 300,
        'enable_compression' => true,
        'max_concurrent_sessions' => 100
    ],
    
    // Monitoring settings
    'monitoring' => [
        'enable_health_checks' => true,
        'enable_performance_metrics' => true,
        'alert_on_high_error_rate' => true,
        'error_rate_threshold' => 0.1
    ]
];
?>
