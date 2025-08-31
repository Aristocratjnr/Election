<?php
/**
 * Payment Form Validation Class
 * Provides comprehensive server-side validation for payment forms
 */

class PaymentValidator {
    private $errors = [];
    private $data = [];
    
    public function __construct($postData) {
        $this->data = $this->sanitizeInput($postData);
    }
    
    /**
     * Sanitize all input data
     */
    private function sanitizeInput($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    /**
     * Validate all form fields
     */
    public function validateForm() {
        $this->validateName('firstName', 'First Name');
        $this->validateName('lastName', 'Last Name');
        $this->validateEmail('email');
        $this->validateOrganization('organization');
        $this->validatePlanSelection();
        $this->validateBillingFrequency();
        $this->validatePaymentMethod();
        $this->validateTermsAcceptance();
        
        // Additional security checks
        $this->performSecurityChecks();
        
        return empty($this->errors);
    }
    
    /**
     * Validate name fields
     */
    private function validateName($field, $label) {
        $value = $this->data[$field] ?? '';
        
        if (empty($value)) {
            $this->errors[$field] = "$label is required";
            return;
        }
        
        if (strlen($value) < 2) {
            $this->errors[$field] = "$label must be at least 2 characters long";
            return;
        }
        
        if (strlen($value) > 50) {
            $this->errors[$field] = "$label must not exceed 50 characters";
            return;
        }
        
        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $value)) {
            $this->errors[$field] = "$label can only contain letters, spaces, hyphens, and apostrophes";
            return;
        }
        
        // Check for suspicious patterns
        if (preg_match('/(.)\1{4,}/', $value)) {
            $this->errors[$field] = "$label contains suspicious repeated characters";
            return;
        }
    }
    
    /**
     * Validate email address
     */
    private function validateEmail($field) {
        $email = $this->data[$field] ?? '';
        
        if (empty($email)) {
            $this->errors[$field] = "Email address is required";
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Please enter a valid email address";
            return;
        }
        
        // Check email length
        if (strlen($email) > 254) {
            $this->errors[$field] = "Email address is too long";
            return;
        }
        
        // Check for suspicious email patterns
        $suspiciousPatterns = [
            '/^[0-9]+@/',  // Starts with only numbers
            '/@[0-9]+\./',  // Domain starts with numbers
            '/\.\./',       // Double dots
            '/^\.|\.$|@\.|\.@/'  // Dots at wrong positions
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $email)) {
                $this->errors[$field] = "Please enter a valid email address";
                return;
            }
        }
        
        // Check for temporary email domains
        $tempDomains = [
            'tempmail.com', '10minutemail.com', 'guerrillamail.com',
            'mailinator.com', 'yopmail.com', 'throwaway.email'
        ];
        
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        if (in_array($domain, $tempDomains)) {
            $this->errors[$field] = "Please use a permanent email address";
            return;
        }
    }
    
    /**
     * Validate organization name
     */
    private function validateOrganization($field) {
        $value = $this->data[$field] ?? '';
        
        // Organization is optional, but if provided, validate it
        if (!empty($value)) {
            if (strlen($value) < 2) {
                $this->errors[$field] = "Organization name must be at least 2 characters long";
                return;
            }
            
            if (strlen($value) > 100) {
                $this->errors[$field] = "Organization name must not exceed 100 characters";
                return;
            }
            
            if (!preg_match('/^[a-zA-Z0-9\s\-_.&()]+$/', $value)) {
                $this->errors[$field] = "Organization name contains invalid characters";
                return;
            }
            
            // Check for HTML/script injection attempts
            if (preg_match('/<[^>]*>/', $value)) {
                $this->errors[$field] = "HTML tags are not allowed in organization name";
                return;
            }
        }
    }
    
    /**
     * Validate plan selection
     */
    private function validatePlanSelection() {
        $plan = $this->data['plan-selection'] ?? '';
        $validPlans = ['team', 'enterprise'];
        
        if (empty($plan)) {
            $this->errors['plan-selection'] = "Please select a plan";
            return;
        }
        
        if (!in_array($plan, $validPlans)) {
            $this->errors['plan-selection'] = "Invalid plan selected";
            return;
        }
    }
    
    /**
     * Validate billing frequency
     */
    private function validateBillingFrequency() {
        $billing = $this->data['billing-frequency'] ?? '';
        $validBilling = ['monthly', 'annual'];
        
        if (empty($billing)) {
            $this->errors['billing-frequency'] = "Please select a billing frequency";
            return;
        }
        
        if (!in_array($billing, $validBilling)) {
            $this->errors['billing-frequency'] = "Invalid billing frequency selected";
            return;
        }
    }
    
    /**
     * Validate payment method specific fields
     */
    private function validatePaymentMethod() {
        $paymentMethod = $this->data['payment-method'] ?? '';
        
        switch ($paymentMethod) {
            case 'credit-card':
                $this->validateCreditCard();
                break;
            case 'mobile-money':
                $this->validateMobileMoney();
                break;
            case 'paypal':
                // PayPal validation handled by PayPal
                break;
            default:
                $this->errors['payment-method'] = "Please select a payment method";
        }
    }
    
    /**
     * Validate credit card details
     */
    private function validateCreditCard() {
        $this->validateCardNumber();
        $this->validateExpiryDate();
        $this->validateCVV();
    }
    
    /**
     * Validate card number using Luhn algorithm
     */
    private function validateCardNumber() {
        $cardNumber = preg_replace('/\s/', '', $this->data['cardNumber'] ?? '');
        
        if (empty($cardNumber)) {
            $this->errors['cardNumber'] = "Card number is required";
            return;
        }
        
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            $this->errors['cardNumber'] = "Card number must be 13-19 digits";
            return;
        }
        
        if (!$this->luhnCheck($cardNumber)) {
            $this->errors['cardNumber'] = "Invalid card number";
            return;
        }
    }
    
    /**
     * Luhn algorithm implementation
     */
    private function luhnCheck($cardNumber) {
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = intval($cardNumber[$i]);
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return ($sum % 10) == 0;
    }
    
    /**
     * Validate expiry date
     */
    private function validateExpiryDate() {
        $expiry = $this->data['expiryDate'] ?? '';
        
        if (empty($expiry)) {
            $this->errors['expiryDate'] = "Expiry date is required";
            return;
        }
        
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $matches)) {
            $this->errors['expiryDate'] = "Please enter a valid expiry date (MM/YY)";
            return;
        }
        
        $month = intval($matches[1]);
        $year = intval($matches[2]) + 2000;
        
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
            $this->errors['expiryDate'] = "Card has expired";
            return;
        }
        
        if ($year > $currentYear + 20) {
            $this->errors['expiryDate'] = "Invalid expiry year";
            return;
        }
    }
    
    /**
     * Validate CVV
     */
    private function validateCVV() {
        $cvv = $this->data['cvv'] ?? '';
        
        if (empty($cvv)) {
            $this->errors['cvv'] = "CVV is required";
            return;
        }
        
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            $this->errors['cvv'] = "CVV must be 3 or 4 digits";
            return;
        }
    }
    
    /**
     * Validate mobile money details
     */
    private function validateMobileMoney() {
        $provider = $this->data['mobileProvider'] ?? '';
        $number = $this->data['mobileNumber'] ?? '';
        
        $validProviders = ['mtn', 'vodafone', 'airtel'];
        
        if (empty($provider)) {
            $this->errors['mobileProvider'] = "Please select a mobile money provider";
        } elseif (!in_array($provider, $validProviders)) {
            $this->errors['mobileProvider'] = "Invalid mobile money provider";
        }
        
        if (empty($number)) {
            $this->errors['mobileNumber'] = "Mobile number is required";
        } elseif (!preg_match('/^0[2-9][0-9]{8}$/', $number)) {
            $this->errors['mobileNumber'] = "Please enter a valid Ghana mobile number";
        }
    }
    
    /**
     * Validate terms acceptance
     */
    private function validateTermsAcceptance() {
        $terms = $this->data['termsConditions'] ?? '';
        
        if ($terms !== 'on' && $terms !== '1' && $terms !== true) {
            $this->errors['termsConditions'] = "You must accept the Terms & Conditions";
        }
    }
    
    /**
     * Perform additional security checks
     */
    private function performSecurityChecks() {
        // Rate limiting check (basic implementation)
        $this->checkRateLimit();
        
        // Honeypot field check
        $this->checkHoneypot();
        
        // User agent validation
        $this->validateUserAgent();
        
        // Referrer validation
        $this->validateReferrer();
    }
    
    /**
     * Basic rate limiting
     */
    private function checkRateLimit() {
        session_start();
        $now = time();
        $attempts = $_SESSION['payment_attempts'] ?? [];
        
        // Remove attempts older than 1 hour
        $attempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 3600;
        });
        
        // Check if more than 5 attempts in the last hour
        if (count($attempts) >= 5) {
            $this->errors['rate_limit'] = "Too many payment attempts. Please try again later.";
        }
        
        // Add current attempt
        $attempts[] = $now;
        $_SESSION['payment_attempts'] = $attempts;
    }
    
    /**
     * Check honeypot field
     */
    private function checkHoneypot() {
        $honeypot = $this->data['website'] ?? '';
        if (!empty($honeypot)) {
            $this->errors['security'] = "Security validation failed";
        }
    }
    
    /**
     * Validate user agent
     */
    private function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (empty($userAgent)) {
            $this->errors['user_agent'] = "Invalid request";
            return;
        }
        
        // Check for suspicious user agents
        $suspiciousAgents = ['bot', 'crawler', 'spider', 'scraper'];
        $userAgentLower = strtolower($userAgent);
        
        foreach ($suspiciousAgents as $agent) {
            if (strpos($userAgentLower, $agent) !== false) {
                $this->errors['user_agent'] = "Invalid request source";
                return;
            }
        }
    }
    
    /**
     * Validate referrer
     */
    private function validateReferrer() {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        // Allow empty referrer (direct access) but validate if present
        if (!empty($referrer)) {
            $referrerHost = parse_url($referrer, PHP_URL_HOST);
            if ($referrerHost !== $host) {
                // Log suspicious activity but don't block (might be legitimate)
                error_log("Payment form accessed from external referrer: $referrer");
            }
        }
    }
    
    /**
     * Get all validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get sanitized data
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Get specific error
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Check if a specific field has error
     */
    public function hasError($field) {
        return isset($this->errors[$field]);
    }
    
    /**
     * Get formatted error messages for display
     */
    public function getFormattedErrors() {
        $formatted = [];
        foreach ($this->errors as $field => $message) {
            $formatted[] = [
                'field' => $field,
                'message' => $message
            ];
        }
        return $formatted;
    }
}
?>
