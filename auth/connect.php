<?php
$host = "127.0.0.1:3306";
$user = "u624234673_new_prep";
$pass = "Prepsaathi@12345";
$db   = "u624234673_new_prep"; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
