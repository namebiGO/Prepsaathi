<?php
session_start();
include __DIR__ . '/../db.php';

$baseUrl = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost')
    ? '/prepsaathi'
    : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prepsaathi- The Companion You Need to Succeed.</title>

    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/style.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/auth/authStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-papm6Q+ua6QJQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQwQKQvQw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <header class="site-header">
        <a style="text-decoration: none;" href="<?php echo $baseUrl; ?>/index.php">
         <div class="logo">
            <img src="<?php echo $baseUrl; ?>/images/logo.png" alt="PrepSaathi" class="logo-img">
            <span class="logo-text">Prep <span class="highlight">Saathi.in</span>
            </div>
        </a>

        <nav class="nav-menu">
            <a href="/" class="active">Home</a>
            <a href="prepsaathi/about.php">About</a>
            <a href="/contact.php">Contact Us</a>
            <?php if (isset($_SESSION['email'])): ?>

                <div class="dropdown">
                    <a href="#">My account ▼</a>
                    <div class="dropdown-content">
                        <a href="auth/homepage.php">My Profile</a>
                        <a href="auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>

                <div class="dropdown">
                    <a href="#">My account ▼</a>
                    <div class="dropdown-content">
                        <a href="auth/index.php">Login</a>
                        <!-- <a href="auth/register.php">Register</a> -->
                    </div>
                </div>
            <?php endif; ?>
        </nav>
    </header>