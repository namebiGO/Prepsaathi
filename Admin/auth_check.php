<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
