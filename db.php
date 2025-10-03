<?php
// Detect if running locally or on production
$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($isLocal) {
    // Local development configuration
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "u624234673_new_prep";
} else {
    // Production configuration
    $host = "127.0.0.1:3306";
    $user = "u624234673_new_prep";
    $pass = "Prepsaathi@12345";
    $db   = "u624234673_new_prep";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>