<?php
require 'vendor/autoload.php';
require_once "db.php";
use Razorpay\Api\Api;

$config = require 'config.php';

// $conn = new mysqli("localhost", "root", "", "ebooks_db");
// if ($conn->connect_error) {
//     die("Database connection failed: " . $conn->connect_error);
// }

$user_id = $_POST['user_id'];
$ebook_id = $_POST['ebook_id'];

$stmt = $conn->prepare("SELECT offer_price FROM ebooks WHERE id = ?");

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $ebook_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ebook not found with ID " . htmlspecialchars($ebook_id));
}

$row = $result->fetch_assoc();
$price_in_rupees = $row['offer_price'];
$amount_in_paise = $price_in_rupees * 100;

$api = new Api($config['razorpay_key_id'], $config['razorpay_key_secret']);


$orderData = [
    'receipt'         => 'order_rcpt_' . time(),
    'amount'          => $amount_in_paise,
    'currency'        => 'INR',
    'payment_capture' => 1
];

try {
    $razorpayOrder = $api->order->create($orderData);
} catch (Exception $e) {
    die("Razorpay order creation failed: " . $e->getMessage());
}


$stmt = $conn->prepare("INSERT INTO orders (user_id, ebook_id, razorpay_order_id, amount, status) VALUES (?, ?, ?, ?, 'created')");
if ($stmt === false) {
    die("Prepare failed for insert: " . $conn->error);
}
$order_id = $razorpayOrder->id;

$stmt->bind_param(
    "iisi",
    $user_id,
    $ebook_id,
    $order_id,
    $amount_in_paise
);
$stmt->execute();


echo json_encode([
    'order_id' => $order_id,
    'amount' => $amount_in_paise,
    'key' => $config['razorpay_key_id']
]);
