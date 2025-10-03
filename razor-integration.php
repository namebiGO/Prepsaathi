<?php
// razorpay-integration.php

class RazorpayIntegration {
    private $keyId;
    private $keySecret;
    private $webhookSecret;
    private $conn;
    
    public function __construct($keyId, $keySecret, $webhookSecret, $dbConnection) {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
        $this->webhookSecret = $webhookSecret;
        $this->conn = $dbConnection;
    }
    
    // Create order in Razorpay and store locally
    public function createOrder($amount, $userId, $ebookId, $receipt = null) {
        $orderData = [
            'amount' => $amount * 100, // Convert to paise
            'currency' => 'INR',
            'receipt' => $receipt ?: 'order_' . time(),
            'payment_capture' => 1
        ];
        
        $response = $this->makeApiCall('/orders', 'POST', $orderData);
        
        if ($response && isset($response['id'])) {
            // Store order in database
            $stmt = $this->conn->prepare("
                INSERT INTO orders (user_id, ebook_id, amount, razorpay_order_id, payment_status) 
                VALUES (?, ?, ?, ?, 'created')
            ");
            $stmt->bind_param("iids", $userId, $ebookId, $amount, $response['id']);
            $stmt->execute();
            
            return $response;
        }
        
        return false;
    }
    
    // Verify payment signature
    public function verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
        $body = $razorpayOrderId . "|" . $razorpayPaymentId;
        $expectedSignature = hash_hmac('sha256', $body, $this->keySecret);
        
        if ($expectedSignature === $razorpaySignature) {
            // Update payment status
            $this->updatePaymentStatus($razorpayPaymentId, $razorpayOrderId);
            return true;
        }
        
        return false;
    }
    
    // Update payment status in database
    private function updatePaymentStatus($paymentId, $orderId) {
        // Fetch payment details from Razorpay
        $payment = $this->makeApiCall("/payments/$paymentId", 'GET');
        
        if ($payment) {
            // Update orders table
            $stmt = $this->conn->prepare("
                UPDATE orders SET 
                razorpay_payment_id = ?, 
                payment_status = ? 
                WHERE razorpay_order_id = ?
            ");
            $stmt->bind_param("sss", $paymentId, $payment['status'], $orderId);
            $stmt->execute();
            
            // Insert into razorpay_payments table
            $this->storePaymentDetails($payment);
            
            // If payment is captured, create purchase record
            if ($payment['status'] === 'captured') {
                $this->createPurchaseRecord($orderId, $paymentId);
            }
        }
    }
    
    // Store detailed payment information
    private function storePaymentDetails($payment) {
        $stmt = $this->conn->prepare("
            INSERT INTO razorpay_payments 
            (razorpay_payment_id, razorpay_order_id, amount, currency, status, method, email, contact, fee, tax, captured) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), captured = VALUES(captured), updated_at = CURRENT_TIMESTAMP
        ");
        
        $captured = $payment['captured'] ? 1 : 0;
        $stmt->bind_param("sssissssiib", 
            $payment['id'],
            $payment['order_id'],
            $payment['amount'],
            $payment['currency'],
            $payment['status'],
            $payment['method'],
            $payment['email'],
            $payment['contact'],
            $payment['fee'],
            $payment['tax'],
            $captured
        );
        $stmt->execute();
    }
    
    // Create purchase record when payment is successful
    private function createPurchaseRecord($orderId, $paymentId) {
        $stmt = $this->conn->prepare("
            INSERT INTO purchases (user_id, ebook_id, payment_id, purchase_date)
            SELECT user_id, ebook_id, ?, NOW()
            FROM orders 
            WHERE razorpay_order_id = ?
        ");
        $stmt->bind_param("ss", $paymentId, $orderId);
        $stmt->execute();
    }
    
    // Sync all payments from Razorpay (for initial setup or recovery)
    public function syncAllPayments($count = 100) {
        $payments = $this->makeApiCall("/payments?count=$count", 'GET');
        
        if ($payments && isset($payments['items'])) {
            foreach ($payments['items'] as $payment) {
                $this->storePaymentDetails($payment);
            }
            return count($payments['items']);
        }
        
        return 0;
    }
    
    // Sync settlements
    public function syncSettlements($count = 100) {
        $settlements = $this->makeApiCall("/settlements?count=$count", 'GET');
        
        if ($settlements && isset($settlements['items'])) {
            foreach ($settlements['items'] as $settlement) {
                $this->storeSettlement($settlement);
            }
            return count($settlements['items']);
        }
        
        return 0;
    }
    
    private function storeSettlement($settlement) {
        $stmt = $this->conn->prepare("
            INSERT INTO razorpay_settlements (settlement_id, amount, fees, tax, status) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("siiis", 
            $settlement['id'],
            $settlement['amount'],
            $settlement['fees'],
            $settlement['tax'],
            $settlement['status']
        );
        $stmt->execute();
    }
    
    // Handle webhook notifications
    public function handleWebhook($payload, $signature) {
        // Verify webhook signature
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            return false;
        }
        
        $data = json_decode($payload, true);
        
        // Log webhook for debugging
        $this->logWebhook($data);
        
        // Process based on event type
        switch ($data['event']) {
            case 'payment.captured':
                $this->handlePaymentCaptured($data['payload']['payment']['entity']);
                break;
                
            case 'payment.failed':
                $this->handlePaymentFailed($data['payload']['payment']['entity']);
                break;
                
            case 'refund.created':
                $this->handleRefundCreated($data['payload']['refund']['entity']);
                break;
                
            case 'settlement.processed':
                $this->handleSettlementProcessed($data['payload']['settlement']['entity']);
                break;
        }
        
        return true;
    }
    
    private function verifyWebhookSignature($payload, $signature) {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
    
    private function logWebhook($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO webhook_logs (event_type, payment_id, order_id, payload) 
            VALUES (?, ?, ?, ?)
        ");
        $paymentId = $data['payload']['payment']['entity']['id'] ?? null;
        $orderId = $data['payload']['payment']['entity']['order_id'] ?? null;
        $stmt->bind_param("ssss", $data['event'], $paymentId, $orderId, json_encode($data));
        $stmt->execute();
    }
    
    private function handlePaymentCaptured($payment) {
        $this->storePaymentDetails($payment);
        $this->createPurchaseRecord($payment['order_id'], $payment['id']);
    }
    
    private function handlePaymentFailed($payment) {
        $this->storePaymentDetails($payment);
        // Update order status to failed
        $stmt = $this->conn->prepare("UPDATE orders SET payment_status = 'failed' WHERE razorpay_order_id = ?");
        $stmt->bind_param("s", $payment['order_id']);
        $stmt->execute();
    }
    
    private function handleRefundCreated($refund) {
        $stmt = $this->conn->prepare("
            INSERT INTO razorpay_refunds (refund_id, payment_id, amount, currency, status, speed) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("ssssss", 
            $refund['id'],
            $refund['payment_id'],
            $refund['amount'],
            $refund['currency'],
            $refund['status'],
            $refund['speed']
        );
        $stmt->execute();
    }
    
    private function handleSettlementProcessed($settlement) {
        $this->storeSettlement($settlement);
    }
    
    // Get dashboard statistics
    public function getDashboardStats() {
        $stats = [];
        
        // Total revenue
        $result = $this->conn->query("
            SELECT SUM(amount/100) as total_revenue 
            FROM razorpay_payments 
            WHERE status = 'captured'
        ");
        $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;
        
        // Total orders
        $result = $this->conn->query("
            SELECT COUNT(*) as total_orders 
            FROM razorpay_payments 
            WHERE status = 'captured'
        ");
        $stats['total_orders'] = $result->fetch_assoc()['total_orders'] ?? 0;
        
        // Total refunds
        $result = $this->conn->query("
            SELECT COUNT(*) as total_refunds, SUM(amount/100) as refund_amount 
            FROM razorpay_refunds
        ");
        $refunds = $result->fetch_assoc();
        $stats['total_refunds'] = $refunds['total_refunds'] ?? 0;
        $stats['refund_amount'] = $refunds['refund_amount'] ?? 0;
        
        // Recent transactions
        $result = $this->conn->query("
            SELECT * FROM razorpay_payments 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stats['recent_transactions'] = $result->fetch_all(MYSQLI_ASSOC);
        
        return $stats;
    }
    
    // Make API call to Razorpay
    private function makeApiCall($endpoint, $method = 'GET', $data = null) {
        $url = 'https://api.razorpay.com/v1' . $endpoint;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_CUSTOMREQUEST => $method,
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        return false;
    }
}

// Configuration file (config/razorpay.php)
define('RAZORPAY_KEY_ID', 'rzp_test_your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_key_secret');
define('RAZORPAY_WEBHOOK_SECRET', 'your_webhook_secret');

// Initialize Razorpay integration
$razorpay = new RazorpayIntegration(
    RAZORPAY_KEY_ID, 
    RAZORPAY_KEY_SECRET, 
    RAZORPAY_WEBHOOK_SECRET, 
    $conn
);
?>


<!-- webhook end point -->


<?php
// webhook.php - Endpoint to receive Razorpay webhooks

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once 'db.php';
require_once 'razorpay-integration.php';

try {
    // Get webhook payload and signature
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
    
    if (empty($payload) || empty($signature)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }
    
    // Initialize Razorpay integration
    $razorpay = new RazorpayIntegration(
        RAZORPAY_KEY_ID, 
        RAZORPAY_KEY_SECRET, 
        RAZORPAY_WEBHOOK_SECRET, 
        $conn
    );
    
    // Process webhook
    if ($razorpay->handleWebhook($payload, $signature)) {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid signature']);
    }
    
} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>


<!-- updated payment flow -->

<?php
// create-order.php - Updated with full integration

require_once 'db.php';
require_once 'razorpay-integration.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$ebookId = $_POST['ebook_id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get ebook details
$stmt = $conn->prepare("SELECT title, offer_price FROM ebooks WHERE id = ?");
$stmt->bind_param("i", $ebookId);
$stmt->execute();
$ebook = $stmt->get_result()->fetch_assoc();

if (!$ebook) {
    echo json_encode(['error' => 'Ebook not found']);
    exit;
}

// Initialize Razorpay
$razorpay = new RazorpayIntegration(
    RAZORPAY_KEY_ID, 
    RAZORPAY_KEY_SECRET, 
    RAZORPAY_WEBHOOK_SECRET, 
    $conn
);

// Create order
$amount = $ebook['offer_price'];
$receipt = "ebook_{$ebookId}_user_{$userId}_" . time();

$order = $razorpay->createOrder($amount, $userId, $ebookId, $receipt);

if ($order) {
    echo json_encode([
        'key' => RAZORPAY_KEY_ID,
        'amount' => $order['amount'],
        'currency' => $order['currency'],
        'order_id' => $order['id'],
        'name' => 'PrepSaathi.in',
        'description' => $ebook['title'],
        'prefill' => [
            'email' => $_SESSION['email'] ?? '',
            'contact' => $_SESSION['phone'] ?? ''
        ]
    ]);
} else {
    echo json_encode(['error' => 'Failed to create order']);
}
?>

<?php
// verify-payment.php - Updated verification

require_once 'db.php';
require_once 'razorpay-integration.php';

session_start();

$input = json_decode(file_get_contents('php://input'), true);

$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';

if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Initialize Razorpay
$razorpay = new RazorpayIntegration(
    RAZORPAY_KEY_ID, 
    RAZORPAY_KEY_SECRET, 
    RAZORPAY_WEBHOOK_SECRET, 
    $conn
);

// Verify payment
if ($razorpay->verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Payment verification failed']);
}
?>


<!-- dashboard page -->

<?php
// admin/dashboard.php

require_once '../db.php';
require_once '../razorpay-integration.php';

session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /');
    exit;
}

// Initialize Razorpay
$razorpay = new RazorpayIntegration(
    RAZORPAY_KEY_ID, 
    RAZORPAY_KEY_SECRET, 
    RAZORPAY_WEBHOOK_SECRET, 
    $conn
);

// Handle sync requests
if ($_POST['action'] ?? '' === 'sync_payments') {
    $synced = $razorpay->syncAllPayments(100);
    $message = "Synced $synced payments";
}

if ($_POST['action'] ?? '' === 'sync_settlements') {
    $synced = $razorpay->syncSettlements(50);
    $message = "Synced $synced settlements";
}

// Get dashboard statistics
$stats = $razorpay->getDashboardStats();

include '../partials/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Dashboard - PrepSaathi</title>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .sync-controls {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .sync-btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .transactions-table th,
        .transactions-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .transactions-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status.captured { background: #d4edda; color: #155724; }
        .status.failed { background: #f8d7da; color: #721c24; }
        .status.pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Payment Dashboard</h1>
        
        <?php if (isset($message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Sync Controls -->
        <div class="sync-controls">
            <h3>Sync Data from Razorpay</h3>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="sync_payments">
                <button type="submit" class="sync-btn">🔄 Sync Payments</button>
            </form>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="sync_settlements">
                <button type="submit" class="sync-btn">🏦 Sync Settlements</button>
            </form>
            <p style="margin-top: 10px; color: #666; font-size: 0.9rem;">
                Last sync: <span id="lastSync"><?= date('Y-m-d H:i:s') ?></span>
            </p>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">₹<?= number_format($stats['total_revenue'], 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_orders'] ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_refunds'] ?></div>
                <div class="stat-label">Total Refunds</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₹<?= number_format($stats['refund_amount'], 2) ?></div>
                <div class="stat-label">Refund Amount</div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <h2>Recent Transactions</h2>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Email</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['recent_transactions'] as $txn): ?>
                <tr>
                    <td><?= htmlspecialchars($txn['razorpay_payment_id']) ?></td>
                    <td>₹<?= number_format($txn['amount'] / 100, 2) ?></td>
                    <td><span class="status <?= $txn['status'] ?>"><?= ucfirst($txn['status']) ?></span></td>
                    <td><?= htmlspecialchars($txn['method'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($txn['email'] ?? 'N/A') ?></td>
                    <td><?= date('M j, Y H:i', strtotime($txn['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: center;">
            <button onclick="autoRefresh()" class="sync-btn">🔄 Enable Auto-Refresh (30s)</button>
        </div>
    </div




   <!-- corn for regular sync -->


   <?php
// cron/sync-razorpay.php
// Run this script every 5 minutes via cron job

require_once '../db.php';
require_once '../razorpay-integration.php';

// Initialize Razorpay
$razorpay = new RazorpayIntegration(
    RAZORPAY_KEY_ID, 
    RAZORPAY_KEY_SECRET, 
    RAZORPAY_WEBHOOK_SECRET, 
    $conn
);

echo "Starting Razorpay sync at " . date('Y-m-d H:i:s') . "\n";

try {
    // Sync recent payments (last 100)
    $paymentsSynced = $razorpay->syncAllPayments(100);
    echo "Synced $paymentsSynced payments\n";
    
    // Sync recent settlements (last 50)
    $settlementsSynced = $razorpay->syncSettlements(50);
    echo "Synced $settlementsSynced settlements\n";
    
    // Log successful sync
    $stmt = $conn->prepare("
        INSERT INTO sync_logs (sync_type, records_synced, status, created_at) 
        VALUES ('payments', ?, 'success', NOW()), ('settlements', ?, 'success', NOW())
    ");
    $stmt->bind_param("ii", $paymentsSynced, $settlementsSynced);
    $stmt->execute();
    
    echo "Sync completed successfully\n";
    
} catch (Exception $e) {
    echo "Sync failed: " . $e->getMessage() . "\n";
    
    // Log failed sync
    $stmt = $conn->prepare("
        INSERT INTO sync_logs (sync_type, status, error_message, created_at) 
        VALUES ('razorpay', 'failed', ?, NOW())
    ");
    $stmt->bind_param("s", $e->getMessage());
    $stmt->execute();
}

// Add this table for tracking sync logs
/*
CREATE TABLE IF NOT EXISTS sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_type VARCHAR(50) NOT NULL,
    records_synced INT DEFAULT 0,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
*/
?>




<!--  configure -->

<?php
// config/razorpay.php

// Razorpay Configuration
// Get these from your Razorpay Dashboard

// Test Mode (for development)
define('RAZORPAY_MODE', 'test'); // 'test' or 'live'

if (RAZORPAY_MODE === 'test') {
    // Test API Keys
    define('RAZORPAY_KEY_ID', 'rzp_test_your_test_key_id');
    define('RAZORPAY_KEY_SECRET', 'your_test_key_secret');
    define('RAZORPAY_WEBHOOK_SECRET', 'your_test_webhook_secret');
} else {
    // Live API Keys
    define('RAZORPAY_KEY_ID', 'rzp_live_your_live_key_id');
    define('RAZORPAY_KEY_SECRET', 'your_live_key_secret');
    define('RAZORPAY_WEBHOOK_SECRET', 'your_live_webhook_secret');
}

// Webhook URL
define('RAZORPAY_WEBHOOK_URL', 'https://prepsaathi.in/webhook.php');

// Other settings
define('RAZORPAY_CURRENCY', 'INR');
define('RAZORPAY_TIMEOUT', 600); // 10 minutes
define('RAZORPAY_THEME_COLOR', '#007cba'); // Your brand color

// Include this file in your main files
// require_once 'config/razorpay.php';
?>



<!-- test script -->

<?php
// test/test-razorpay.php
// Run this script to test your Razorpay integration

require_once '../db.php';
require_once '../config/razorpay.php';
require_once '../razorpay-integration.php';

echo "<h1>Razorpay Integration Test</h1>";

// Initialize Razorpay
$razorpay = new RazorpayIntegration(
    RAZORPAY_KEY_ID, 
    RAZORPAY_KEY_SECRET, 
    RAZORPAY_WEBHOOK_SECRET, 
    $conn
);

echo "<h2>1. Testing API Connection</h2>";

try {
    // Test API connection by fetching payments
    $payments = $razorpay->syncAllPayments(1);
    echo "✅ API Connection successful<br>";
} catch (Exception $e) {
    echo "❌ API Connection failed: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Testing Order Creation</h2>";

try {
    // Test order creation
    $testOrder = $razorpay->createOrder(100, 1, 1, 'test_order_' . time());
    if ($testOrder) {
        echo "✅ Order creation successful. Order ID: " . $testOrder['id'] . "<br>";
    } else {
        echo "❌ Order creation failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Order creation failed: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Testing Database Tables</h2>";

$tables = [
    'razorpay_payments',
    'razorpay_settlements', 
    'razorpay_refunds',
    'webhook_logs'
];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' missing<br>";
    }
}

echo "<h2>4. Testing Dashboard Stats</h2>";

try {
    $stats = $razorpay->getDashboardStats();
    echo "✅ Dashboard stats retrieved successfully<br>";
    echo "Total Revenue: ₹" . number_format($stats['total_revenue'], 2) . "<br>";
    echo "Total Orders: " . $stats['total_orders'] . "<br>";
} catch (Exception $e) {
    echo "❌ Dashboard stats failed: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Configuration Check</h2>";

echo "Key ID: " . (defined('RAZORPAY_KEY_ID') ? '✅ Set' : '❌ Missing') . "<br>";
echo "Key Secret: " . (defined('RAZORPAY_KEY_SECRET') ? '✅ Set' : '❌ Missing') . "<br>";
echo "Webhook Secret: " . (defined('RAZORPAY_WEBHOOK_SECRET') ? '✅ Set' : '❌ Missing') . "<br>";
echo "Mode: " . RAZORPAY_MODE . "<br>";

echo "<h2>6. Webhook URL Test</h2>";
echo "Webhook URL: " . RAZORPAY_WEBHOOK_URL . "<br>";
echo "Make sure this URL is accessible and configured in Razorpay Dashboard<br>";

echo "<h2>Test Complete</h2>";
echo "<p>If all tests pass, your Razorpay integration is ready!</p>";
?>