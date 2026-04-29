<?php

// Disable error display in production (enable only for debugging)
// ini_set('display_errors', 0);
// error_reporting(0);

// Set JSON header
header('Content-Type: application/json');

// Read raw POST data
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE || !$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$amount  = intval($input['amount'] ?? 0);
$leadId  = $input['leadId'] ?? null;
$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');

// Validate amount
if ($amount <= 0 || $amount > 99999999) { // Max $999,999.99
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Validate name
if (empty($name) || strlen($name) > 255) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid name']);
    exit;
}

// Load Stripe SDK
require_once __DIR__ . '/vendor/autoload.php';

// Set your SECRET KEY (NEVER expose this)
$secret_key = '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';

// If you need different keys based on type, uncomment and modify:
// if(isset($_GET['type']) && $_GET['type'] == 'Branded')
// {
//     $secret_key = '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';
// }
// else
// {
//     $secret_key = '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';
// }

\Stripe\Stripe::setApiKey($secret_key);

// Create PaymentIntent
try {

    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount'   => $amount, // amount in cents
        'currency' => 'usd',

        'automatic_payment_methods' => [
            'enabled' => true,
        ],

        'metadata' => [
            'lead_id' => $leadId,
            'name'    => $name,
            'email'   => $email,
        ],

        'receipt_email' => $email,
    ]);

    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    // Log error (implement logging as needed)
    // error_log('Stripe API Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Payment processing failed. Please try again.'
    ]);
} catch (Exception $e) {
    // Log error (implement logging as needed)
    // error_log('General Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'An unexpected error occurred. Please try again.'
    ]);
}
