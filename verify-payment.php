<?php
require 'vendor/autoload.php';
require_once "db.php";

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$config = require 'config.php';
$api = new Api($config['razorpay_key_id'], $config['razorpay_key_secret']);
// $conn = new mysqli("localhost", "root", "", "ebooks_db");

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;



$attributes = [
    'razorpay_order_id' => $input['razorpay_order_id'],
    'razorpay_payment_id' => $input['razorpay_payment_id'],
    'razorpay_signature' => $input['razorpay_signature']
];

try {
    $api->utility->verifyPaymentSignature($attributes);


    $stmt = $conn->prepare("UPDATE orders SET razorpay_payment_id=?, razorpay_signature=?, status='paid' WHERE razorpay_order_id=?");
    $stmt->bind_param("sss", $input['razorpay_payment_id'], $input['razorpay_signature'], $input['razorpay_order_id']);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (SignatureVerificationError $e) {
    $stmt = $conn->prepare("UPDATE orders SET status='failed' WHERE razorpay_order_id=?");
    $stmt->bind_param("s", $input['razorpay_order_id']);
    $stmt->execute();

    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$orderQuery = $conn->prepare("SELECT id, user_id, ebook_id FROM orders WHERE razorpay_order_id=?");
$orderQuery->bind_param("s", $input['razorpay_order_id']);
$orderQuery->execute();
$order = $orderQuery->get_result()->fetch_assoc();

if ($order) {
    $stmt = $conn->prepare("INSERT INTO purchases (user_id, ebook_id, order_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $order['user_id'], $order['ebook_id'], $order['id']);
    $stmt->execute();
}
$ebookId = $ebook_id; // from order
$orderId = $order_id; // from orders table

// Insert into purchases if not already exists
$check = $conn->prepare("SELECT id FROM purchases WHERE user_id = ? AND ebook_id = ?");
$check->bind_param("ii", $userId, $ebookId);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO purchases (user_id, ebook_id, order_id) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $userId, $ebookId, $orderId);
    $insert->execute();
}
