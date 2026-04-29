<?php
ob_start();
require_once 'vendor/autoload.php';

// Configuration - move these to environment variables in production
$config = [
    'db' => [
        'host' => 'localhost',     // Replace with actual values
        'username' => 'trivfhzv_crm',   // Replace with actual values
        'password' => 'trivfhzv_crm',   // Replace with actual values
        'database' => 'trivfhzv_crm'     // Replace with actual values
    ],
    'stripe' => [
        'secret_key' => 'sk_test_51OjBBrHSre3udbF2efuwSW5pO9NuGtKXFmDfm6rH2xnb7ITSQjGbKhcUUdNdQ4jItM9JmMA2EU0NUOhAjxxV9aaU00n6RVh5Vb' // Move to environment variable
    ]
];

/**
 * Write error to log file in stripe_response folder
 */
function writeLogFile($message, $context = []) {
    $log_dir = __DIR__;
    $log_file = $log_dir . '/payment_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = "[{$timestamp}] {$message}";
    
    // Extract exception from context for separate handling
    $exception = null;
    if (isset($context['exception']) && $context['exception'] instanceof Exception) {
        $exception = $context['exception'];
        unset($context['exception']); // Remove from context array for JSON encoding
    }
    
    // Add context information if provided
    if (!empty($context)) {
        $log_entry .= "\nContext: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    // Add exception details if available
    if ($exception) {
        $log_entry .= "\nException: " . get_class($exception) . "\n";
        $log_entry .= "Message: " . $exception->getMessage() . "\n";
        $log_entry .= "File: " . $exception->getFile() . " (Line: " . $exception->getLine() . ")\n";
        $log_entry .= "Stack Trace:\n" . $exception->getTraceAsString();
    }
    
    $log_entry .= "\n" . str_repeat('-', 80) . "\n\n";
    
    // Write to log file (append mode)
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Create database connection with error handling
 */
function createDatabaseConnection($config) {
    $conn = new mysqli(
        $config['db']['host'],
        $config['db']['username'],
        $config['db']['password'],
        $config['db']['database']
    );
    
    if ($conn->connect_error) {
        writeLogFile("Database connection failed: " . $conn->connect_error, [
            'host' => $config['db']['host'],
            'database' => $config['db']['database']
        ]);
        throw new Exception("Database connection failed");
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

/**
 * Validate required GET parameters
 */
function validateRequiredParams($required_params) {
    $missing = [];
    foreach ($required_params as $param) {
        if (!isset($_GET[$param]) || empty($_GET[$param])) {
            $missing[] = $param;
        }
    }
    return $missing;
}

/**
 * Update invoice status to paid
 */
function updateInvoiceStatus($conn, $invoice_id) {
    $stmt = $conn->prepare("UPDATE invoices SET status = 'paid' WHERE invoice_key = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare invoice update statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $invoice_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if (!$result) {
        throw new Exception("Failed to update invoice status: " . $conn->error);
    }
    
    return true;
}

/**
 * Get invoice data with client information
 */
function getInvoiceData($conn, $invoice_id) {
    $stmt = $conn->prepare("
        SELECT i.*, c.* 
        FROM invoices i 
        JOIN clients c ON c.id = i.clientid 
        WHERE i.invoice_key = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare invoice select statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        throw new Exception("Invoice not found");
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Insert payment record
 */
function insertPaymentRecord($conn, $invoice_data, $payment_id, $response, $brand_id) {
    // Escape all string values to prevent SQL injection
    $team_key = mysqli_real_escape_string($conn, $invoice_data['team_key']);
    $creatorid = intval($invoice_data['creatorid']);
    $agent_id = intval($invoice_data['agent_id']);
    $clientid = intval($invoice_data['clientid']);
    $invoice_key = intval($invoice_data['invoice_key']);
    $name = mysqli_real_escape_string($conn, $invoice_data['name']);
    $email = mysqli_real_escape_string($conn, $invoice_data['email']);
    $amount = mysqli_real_escape_string($conn, $invoice_data['final_amount']);
    $payment_id_escaped = mysqli_real_escape_string($conn, $payment_id);
    $response_escaped = mysqli_real_escape_string($conn, $response);
    $brand_id = intval($brand_id);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO payments (
        team_key, brand_key, creatorid, actor_id, actor_type,
        agent_id, clientid, invoice_id, name, email,
        address, amount, authorizenet_transaction_id, payment_response, transaction_response,
        payment_gateway, payment_notes, type, payment_status, payment_status_process_time,
        settlement, settlement_process_time, settlement_response, response_code, auth_id,
        message_code, deleted_at, created_at, updated_at, project_id,
        sales_type, phone, compliance_verified, head_verified, audio,
        compliance_varified_note, merchant_id, card_type, card_name, card_number,
        card_exp_month, card_exp_year, card_cvv, ip, city,
        state, country
    ) VALUES (
        '$team_key', $brand_id, $creatorid, 0, NULL,
        $agent_id, $clientid, $invoice_key, '$name', '$email',
        '', '$amount', '$payment_id_escaped', '$response_escaped', '$response_escaped',
        'Stripe', 'Stripe', 'PPC', '1', NULL,
        'captured', NULL, NULL, '1', '04190Z',
        '1', NULL, '$created_at', '$updated_at', 12879,
        'Fresh', '$email', 0, 0, NULL,
        NULL, 33, 'mastercard', 'Michael Lom', 6136,
        '11', '2029', '000', '0.0.0.0', 'N/A',
        'TX', 'US'
    )";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Failed to insert payment record: " . $conn->error);
    }
    
    return true;
}

// Main execution
try {
    // Validate session_id parameter
    // if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    //     throw new Exception("Session ID is required");
    // }
    
    // Validate other required parameters
    // $missing_params = validateRequiredParams(['invoiceid', 'brandid', 'url']);
    // if (!empty($missing_params)) {
    //     throw new Exception("Missing required parameters: " . implode(', ', $missing_params));
    // }
    
    // Sanitize inputs
    $session_id = filter_var($_GET['session_id'], FILTER_SANITIZE_STRING);
    $invoice_id = filter_var($_GET['invoiceid'], FILTER_VALIDATE_INT);
    $brand_id = filter_var($_GET['brandid'], FILTER_VALIDATE_INT);
    $redirect_url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
    
    // Remove www. from URL if present
    $redirect_url = preg_replace('#^(https?://)www\.#i', '$1', $redirect_url);
    
    if (!$invoice_id || !$brand_id) {
        throw new Exception("Invalid invoice ID or brand ID");
    }
    
    // Initialize Stripe
    $stripe = new \Stripe\StripeClient($config['stripe']['secret_key']);
    
    // Retrieve Stripe session
    // try {
    //     $session = $stripe->checkout->sessions->retrieve(
    //         $session_id,
    //         ['expand' => ['payment_intent', 'invoice', 'line_items']]
    //     );
    // } catch (\Stripe\Exception\ApiErrorException $e) {
    //     writeLogFile("Stripe API error while retrieving session: " . $e->getMessage(), [
    //         'exception' => $e,
    //         'session_id' => $session_id,
    //         'stripe_error_type' => $e->getError()->type ?? 'UNKNOWN',
    //         'stripe_error_code' => $e->getError()->code ?? 'UNKNOWN'
    //     ]);
    //     throw new Exception("Failed to retrieve Stripe session: " . $e->getMessage());
    // }
    
    // if (!$session) {
    //     writeLogFile("Failed to retrieve Stripe session - session is null", [
    //         'session_id' => $session_id
    //     ]);
    //     throw new Exception("Failed to retrieve Stripe session");
    // }
    
    // Get payment ID
    $payment_id = $session->payment_intent->id ?? null;
    // if (!$payment_id) {
    //     writeLogFile("Payment intent ID not found in session", [
    //         'session_id' => $session_id,
    //         'session_status' => $session->status ?? 'UNKNOWN',
    //         'payment_status' => $session->payment_status ?? 'UNKNOWN'
    //     ]);
    //     throw new Exception("Payment intent ID not found in session");
    // }
    
    // Create database connection
    $conn = createDatabaseConnection($config);
    
    // Start transaction
    $conn->autocommit(false);
    
    try {
        // Update invoice status
        updateInvoiceStatus($conn, $invoice_id);
        echo "✅ Invoice status updated successfully.<br>";
        
        // Get invoice data
        $invoice_data = getInvoiceData($conn, $invoice_id);
        
        // Prepare response data
        $response = json_encode($session);
        
        // Insert payment record
        insertPaymentRecord($conn, $invoice_data, $payment_id, $response, $brand_id);
        echo "✅ Payment record inserted successfully.<br>";
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to success URL
        if (filter_var($redirect_url, FILTER_VALIDATE_URL)) {
            header("Location: " . $redirect_url);
            exit;
        } else {
            echo "✅ Payment processed successfully, but redirect URL is invalid.<br>";
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        writeLogFile("Transaction error and rolled back: " . $e->getMessage(), [
            'exception' => $e,
            'invoice_id' => $invoice_id ?? null,
            'brand_id' => $brand_id ?? null,
            'session_id' => $session_id ?? null
        ]);
        throw $e;
    } finally {
        $conn->close();
    }
    
} catch (Exception $e) {
    // Log error to file
    $context = [
        'exception' => $e,
        'request_params' => [
            'session_id' => $_GET['session_id'] ?? null,
            'invoiceid' => $_GET['invoiceid'] ?? null,
            'brandid' => $_GET['brandid'] ?? null,
            'url' => $_GET['url'] ?? null
        ],
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ];
    
    writeLogFile("Payment processing error: " . $e->getMessage(), $context);
    
    // Display user-friendly error message
    echo "❌ Error processing payment: " . htmlspecialchars($e->getMessage()) . "<br>";
    
    // In production, redirect to an error page instead
    // header("Location: /payment-error.php");
    // exit;
}
?>