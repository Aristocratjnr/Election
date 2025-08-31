<?php
/**
 * OpenRouter AI API Integration
 * Handles communication with OpenRouter API for AI-powered chat responses
 */

class OpenRouterAI {
    private $config;
    private $logger;
    
    public function __construct($config, $logger = null) {
        $this->config = $config['openrouter'] ?? [];
        $this->logger = $logger;
    }
    
    /**
     * Generate AI response using OpenRouter API
     */
    public function generateResponse($userMessage, $conversationHistory = []) {
        if (!$this->isEnabled()) {
            throw new Exception('OpenRouter is not enabled');
        }
        
        if (empty($this->config['api_key'])) {
            throw new Exception('OpenRouter API key not configured');
        }
        
        $messages = $this->buildMessageHistory($userMessage, $conversationHistory);
        
        for ($attempt = 1; $attempt <= ($this->config['retry_attempts'] ?? 2); $attempt++) {
            try {
                $response = $this->makeApiRequest($messages);
                
                if ($response && isset($response['choices'][0]['message']['content'])) {
                    $content = trim($response['choices'][0]['message']['content']);
                    
                    // Log successful response
                    $this->log('success', [
                        'attempt' => $attempt,
                        'model' => $response['model'] ?? $this->config['model'],
                        'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                        'response_length' => strlen($content)
                    ]);
                    
                    return $content;
                }
                
            } catch (Exception $e) {
                $this->log('error', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
                
                // If this is the last attempt, throw the exception
                if ($attempt >= ($this->config['retry_attempts'] ?? 2)) {
                    throw $e;
                }
                
                // Wait before retry (exponential backoff)
                sleep($attempt);
            }
        }
        
        throw new Exception('Failed to get response after all retry attempts');
    }
    
    /**
     * Make HTTP request to OpenRouter API
     */
    private function makeApiRequest($messages) {
        $url = $this->config['base_url'];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config['api_key'],
            'HTTP-Referer: ' . ($this->config['site_url'] ?? 'http://localhost'),
            'X-Title: ' . ($this->config['app_name'] ?? 'AI Chat')
        ];
        
        $payload = [
            'model' => $this->config['model'],
            'messages' => $messages,
            'max_tokens' => $this->config['max_tokens'] ?? 300,
            'temperature' => $this->config['temperature'] ?? 0.3,
            'stream' => false
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'] ?? 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ElectionVotingSystem/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: $error");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "HTTP $httpCode error";
            throw new Exception("API error: $errorMessage", $httpCode);
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }
        
        return $data;
    }
    
    /**
     * Build message history for API request
     */
    private function buildMessageHistory($userMessage, $conversationHistory = []) {
        $messages = [];
        
        // System prompt
        $systemPrompt = $this->config['system_prompt'] ?? 'You are a helpful assistant.';
        $votingContext = $this->config['voting_context'] ?? '';
        
        if ($votingContext) {
            $systemPrompt .= "\n\nContext: " . $votingContext;
        }
        
        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];
        
        // Add conversation history (limited)
        $maxHistory = $this->config['max_context_history'] ?? 3;
        $recentHistory = array_slice($conversationHistory, -$maxHistory);
        
        foreach ($recentHistory as $exchange) {
            if (isset($exchange['user'])) {
                $messages[] = [
                    'role' => 'user',
                    'content' => $exchange['user']
                ];
            }
            if (isset($exchange['assistant'])) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $exchange['assistant']
                ];
            }
        }
        
        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        return $messages;
    }
    
    /**
     * Check if OpenRouter is enabled and configured
     */
    public function isEnabled() {
        return !empty($this->config['enabled']) && !empty($this->config['api_key']);
    }
    
    /**
     * Get available models
     */
    public function getAvailableModels() {
        $models = [$this->config['model']];
        if (isset($this->config['alternative_models'])) {
            $models = array_merge($models, $this->config['alternative_models']);
        }
        return array_unique($models);
    }
    
    /**
     * Test API connectivity
     */
    public function testConnection() {
        try {
            $testResponse = $this->generateResponse('Hello, can you help me?');
            return [
                'success' => true,
                'response' => $testResponse,
                'message' => 'OpenRouter API connection successful'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'OpenRouter API connection failed'
            ];
        }
    }
    
    /**
     * Log events
     */
    private function log($level, $data) {
        if ($this->logger) {
            $this->logger->log($level, 'OpenRouter: ' . json_encode($data));
        }
    }
    
    /**
     * Get current model info
     */
    public function getCurrentModel() {
        return $this->config['model'] ?? 'unknown';
    }
    
    /**
     * Get token usage estimate
     */
    public function estimateTokens($text) {
        // Rough estimation: 1 token ≈ 4 characters for English text
        return ceil(strlen($text) / 4);
    }
}
?>
