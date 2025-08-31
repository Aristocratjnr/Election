<?php
/**
 * AI Chat Environment Configuration
 * Modify these settings based on your deployment environment
 */

return [
    // Environment settings
    'environment' => 'development', // 'development', 'staging', 'production'
    
    // Security settings
    'allowed_origins' => [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:3000',
        'http://localhost:8080',
        'https://yourdomain.com' // Add your production domain here
    ],
    
    // Rate limiting
    'rate_limit' => [
        'max_requests_per_minute' => 20,
        'max_requests_per_hour' => 200,
        'enable_rate_limiting' => true
    ],
    
    // Message settings
    'message' => [
        'max_length' => 500,
        'min_length' => 3,
        'allow_html' => false,
        'profanity_filter' => true
    ],
    
    // Response settings
    'response' => [
        'delay_min_ms' => 800,  // Minimum response delay
        'delay_max_ms' => 2000, // Maximum response delay
        'max_length' => 2000,   // Maximum response length
        'enable_artificial_delay' => true
    ],
    
    // Logging settings
    'logging' => [
        'enable_logging' => true,
        'log_file' => '../logs/ai_chat.log',
        'log_level' => 'info', // 'debug', 'info', 'warning', 'error'
        'log_requests' => true,
        'log_responses' => true,
        'log_errors' => true,
        'max_log_file_size' => 10485760, // 10MB
        'log_retention_days' => 30
    ],
    
    // OpenRouter API settings
    'openrouter' => [
        'enabled' => true,
        'api_key' => '', // Add your OpenRouter API key here: get it from https://openrouter.ai/keys
        'base_url' => 'https://openrouter.ai/api/v1/chat/completions',
        'model' => 'anthropic/claude-3-haiku', // Fast and cost-effective
        'alternative_models' => [
            'openai/gpt-3.5-turbo',
            'meta-llama/llama-3-8b-instruct',
            'anthropic/claude-3-sonnet',
            'google/gemma-7b-it'
        ],
        'max_tokens' => 300,
        'temperature' => 0.3, // Lower for consistent responses
        'timeout' => 15, // seconds
        'fallback_to_local' => true,
        'retry_attempts' => 2,
        'system_prompt' => "You are a helpful voting assistant for a student election system. You ONLY answer questions related to voting, elections, candidates, voting process, election security, deadlines, and technical voting issues. For ANY non-voting questions, you MUST respond exactly with: 'Sorry, I can only help with voting-related questions.' Keep responses under 250 words, helpful, and professional. Focus on actionable voting guidance.",
        'voting_context' => "This is a student election portal where students vote for leadership positions using secure blockchain technology.",
        'app_name' => 'ElectionVotingAssistant', // For OpenRouter analytics
        'site_url' => 'http://localhost/Election' // Your site URL
    ],
    
    // AI Chat settings
    'ai' => [
        'personality' => 'helpful', // 'helpful', 'formal', 'friendly'
        'response_style' => 'detailed', // 'brief', 'detailed', 'comprehensive'
        'enable_context_memory' => true,
        'max_context_history' => 5,
        'enable_personalization' => true,
        'enable_suggestions' => true,
        'use_openrouter' => true, // Set to false to use local knowledge base only
        'openrouter_priority' => true // Try OpenRouter first, fallback to local if failed
    ],
    
    // Security features
    'security' => [
        'enable_csrf_protection' => true,
        'enable_input_sanitization' => true,
        'enable_output_encoding' => true,
        'max_session_duration' => 3600, // 1 hour
        'enable_ip_whitelisting' => false,
        'allowed_ips' => []
    ],
    
    // Feature flags
    'features' => [
        'enable_api_endpoint' => true,
        'enable_advanced_nlp' => false, // For future AI integration
        'enable_sentiment_analysis' => false,
        'enable_conversation_export' => false,
        'enable_analytics' => true
    ],
    
    // Database settings (if needed for chat history)
    'database' => [
        'enable_chat_history' => false,
        'history_retention_days' => 7,
        'enable_analytics_storage' => true
    ],
    
    // Performance settings
    'performance' => [
        'enable_caching' => true,
        'cache_duration' => 300, // 5 minutes
        'enable_compression' => true,
        'max_concurrent_sessions' => 100
    ],
    
    // Monitoring settings
    'monitoring' => [
        'enable_health_checks' => true,
        'enable_performance_metrics' => true,
        'alert_on_high_error_rate' => true,
        'error_rate_threshold' => 0.1 // 10%
    ]
];
?>
