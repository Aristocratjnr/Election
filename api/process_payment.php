<?php
/**
 * Payment Processing Endpoint
 * Handles secure payment form submission with comprehensive validation
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS headers for AJAX requests
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_HOST']);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

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

// Include required files
require_once __DIR__ . '/../configs/dbconnection.php';
require_once __DIR__ . '/../classes/PaymentValidator.php';

// Start session for rate limiting
session_start();

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check if request is AJAX
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        
        // For non-AJAX requests, redirect with error
        if (isset($_POST['firstName'])) {
            $_SESSION['payment_error'] = 'Invalid request method';
            header('Location: ../payment-page.php?error=1');
            exit;
        }
        
        throw new Exception('Invalid request method');
    }
    
    // Get and decode JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Fallback to POST data if JSON decode fails
    if ($data === null) {
        $data = $_POST;
    }
    
    // Initialize validator
    $validator = new PaymentValidator($data);
    
    // Validate form data
    if (!$validator->validateForm()) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->getFormattedErrors()
        ]);
        exit;
    }
    
    // Get validated data
    $validatedData = $validator->getData();
    
    // Calculate total amount
    $amounts = [
        'team' => ['monthly' => 29.00, 'annual' => 264.00],
        'enterprise' => ['monthly' => 49.00, 'annual' => 444.00]
    ];
    
    $plan = $validatedData['plan-selection'];
    $billing = $validatedData['billing-frequency'];
    $amount = $amounts[$plan][$billing];
    
    // Create unique transaction ID
    $transactionId = 'TXN_' . time() . '_' . uniqid();
    
    // Prepare customer data
    $customerData = [
        'first_name' => $validatedData['firstName'],
        'last_name' => $validatedData['lastName'],
        'email' => $validatedData['email'],
        'organization' => $validatedData['organization'] ?? null,
        'plan' => $plan,
        'billing_frequency' => $billing,
        'amount' => $amount,
        'currency' => 'GHS',
        'transaction_id' => $transactionId,
        'payment_method' => $validatedData['payment-method'] ?? 'credit-card',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Log payment attempt
    logPaymentAttempt($customerData);
    
    // Process payment based on method
    $paymentResult = processPayment($customerData, $validatedData);
    
    if ($paymentResult['success']) {
        // Payment successful
        $_SESSION['payment_success'] = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'plan' => $plan,
            'billing' => $billing
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'transaction_id' => $transactionId,
            'redirect_url' => 'payment-success.php'
        ]);
    } else {
        // Payment failed
        logPaymentFailure($transactionId, $paymentResult['error']);
        
        http_response_code(402);
        echo json_encode([
            'success' => false,
            'message' => $paymentResult['error'] ?? 'Payment processing failed',
            'transaction_id' => $transactionId
        ]);
    }
    
} catch (Exception $e) {
    // Log error
    error_log('Payment processing error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.',
        'error_code' => 'SERVER_ERROR'
    ]);
}

/**
 * Process payment based on selected method
 */
function processPayment($customerData, $validatedData) {
    $paymentMethod = $customerData['payment_method'];
    
    switch ($paymentMethod) {
        case 'credit-card':
            return processCreditCardPayment($customerData, $validatedData);
        case 'mobile-money':
            return processMobileMoneyPayment($customerData, $validatedData);
        case 'paypal':
            return processPayPalPayment($customerData, $validatedData);
        default:
            return ['success' => false, 'error' => 'Invalid payment method'];
    }
}

/**
 * Process credit card payment
 */
function processCreditCardPayment($customerData, $validatedData) {
    // In a real application, you would integrate with a payment processor
    // like Stripe, PayPal, or Paystack
    
    // For demo purposes, simulate payment processing
    $cardNumber = str_replace(' ', '', $validatedData['cardNumber']);
    $expiryDate = $validatedData['expiryDate'];
    $cvv = $validatedData['cvv'];
    
    // Simulate payment gateway call
    sleep(1); // Simulate processing delay
    
    // Demo: 90% success rate
    $success = (rand(1, 10) <= 9);
    
    if ($success) {
        // Store payment record in database
        $paymentId = storePaymentRecord($customerData, [
            'card_last_four' => substr($cardNumber, -4),
            'card_type' => detectCardType($cardNumber),
            'gateway_response' => 'APPROVED',
            'gateway_transaction_id' => 'CC_' . uniqid()
        ]);
        
        return [
            'success' => true,
            'payment_id' => $paymentId,
            'gateway_transaction_id' => 'CC_' . uniqid()
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Payment declined. Please check your card details and try again.'
        ];
    }
}

/**
 * Process mobile money payment
 */
function processMobileMoneyPayment($customerData, $validatedData) {
    $provider = $validatedData['mobileProvider'];
    $mobileNumber = $validatedData['mobileNumber'];
    
    // Simulate mobile money processing
    sleep(2); // Mobile money usually takes longer
    
    // Demo: 85% success rate
    $success = (rand(1, 20) <= 17);
    
    if ($success) {
        $paymentId = storePaymentRecord($customerData, [
            'mobile_provider' => $provider,
            'mobile_number' => substr($mobileNumber, 0, 3) . '****' . substr($mobileNumber, -3),
            'gateway_response' => 'APPROVED',
            'gateway_transaction_id' => 'MM_' . uniqid()
        ]);
        
        return [
            'success' => true,
            'payment_id' => $paymentId,
            'gateway_transaction_id' => 'MM_' . uniqid()
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Mobile money payment failed. Please ensure you have sufficient balance and try again.'
        ];
    }
}

/**
 * Process PayPal payment
 */
function processPayPalPayment($customerData, $validatedData) {
    // PayPal would require redirect to PayPal for authentication
    // This is a simplified implementation
    
    return [
        'success' => true,
        'redirect_required' => true,
        'redirect_url' => 'https://www.paypal.com/checkoutnow?token=' . uniqid(),
        'message' => 'Redirecting to PayPal for payment...'
    ];
}

/**
 * Store payment record in database
 */
function storePaymentRecord($customerData, $paymentDetails) {
    global $connection;
    
    try {
        $stmt = $connection->prepare("
            INSERT INTO payment_transactions (
                transaction_id, customer_email, customer_name, organization,
                plan_type, billing_frequency, amount, currency,
                payment_method, payment_details, ip_address, user_agent,
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)
        ");
        
        $customerName = $customerData['first_name'] . ' ' . $customerData['last_name'];
        $paymentDetailsJson = json_encode($paymentDetails);
        
        $stmt->bind_param(
            "ssssssdssssss",
            $customerData['transaction_id'],
            $customerData['email'],
            $customerName,
            $customerData['organization'],
            $customerData['plan'],
            $customerData['billing_frequency'],
            $customerData['amount'],
            $customerData['currency'],
            $customerData['payment_method'],
            $paymentDetailsJson,
            $customerData['ip_address'],
            $customerData['user_agent'],
            $customerData['created_at']
        );
        
        $stmt->execute();
        return $connection->insert_id;
        
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
        throw new Exception('Failed to store payment record');
    }
}

/**
 * Log payment attempt
 */
function logPaymentAttempt($customerData) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'transaction_id' => $customerData['transaction_id'],
        'email' => $customerData['email'],
        'plan' => $customerData['plan'],
        'amount' => $customerData['amount'],
        'ip_address' => $customerData['ip_address'],
        'user_agent' => $customerData['user_agent']
    ];
    
    error_log('Payment attempt: ' . json_encode($logData));
}

/**
 * Log payment failure
 */
function logPaymentFailure($transactionId, $error) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'transaction_id' => $transactionId,
        'error' => $error,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    error_log('Payment failure: ' . json_encode($logData));
}

/**
 * Detect card type from card number
 */
function detectCardType($cardNumber) {
    $number = preg_replace('/\D/', '', $cardNumber);
    
    if (preg_match('/^4/', $number)) {
        return 'Visa';
    } elseif (preg_match('/^5[1-5]/', $number)) {
        return 'Mastercard';
    } elseif (preg_match('/^3[47]/', $number)) {
        return 'American Express';
    } elseif (preg_match('/^6/', $number)) {
        return 'Discover';
    }
    
    return 'Unknown';
}
?>
