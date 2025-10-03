<?php
session_start();
include("connect.php");

if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$email = $_SESSION['email'];
$fullName = $_POST['fullName'] ?? '';
$password = $_POST['password'] ?? '';

// Split fullName into first and last names (example)
$nameParts = explode(' ', trim($fullName), 2);
$firstName = $nameParts[0];
$lastName = $nameParts[1] ?? '';

if (!$fullName || !$email) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
    exit;
}

if ($password) {
    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, password=? WHERE email=?");
    $stmt->bind_param("ssss", $firstName, $lastName, $hashedPassword, $email);
} else {
    // No password change
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=? WHERE email=?");
    $stmt->bind_param("sss", $firstName, $lastName, $email);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
