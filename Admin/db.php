<?php
$servername = "localhost";  // or "127.0.0.1"
$username   = "root";       // default XAMPP user
$password   = "";           // default password is empty
$dbname     = "u624234673_new_prep"; // your local database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
