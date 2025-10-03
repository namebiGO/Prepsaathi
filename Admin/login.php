<?php
session_start();
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // change these before going live
    $valid_user = "admin";
    $valid_pass = "Admin123";

    if ($username === $valid_user && $password === $valid_pass) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
    }
    .login-box {
        background: #fff;
        padding: 40px 30px;
        border-radius: 12px;
        width: 350px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        text-align: center;
        animation: fadeIn 0.7s ease-in-out;
    }
    .login-box h2 {
        margin-bottom: 25px;
        color: #4f46e5;
    }
    .input-group {
        margin-bottom: 15px;
        text-align: left;
    }
    .input-group label {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        margin-bottom: 6px;
        display: block;
    }
    .input-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .input-group input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.2);
        outline: none;
    }
    button {
        width: 100%;
        padding: 12px;
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background: #4338ca;
    }
    .error {
        color: red;
        margin-top: 12px;
        font-size: 14px;
    }
    .footer {
        margin-top: 20px;
        font-size: 13px;
        color: #777;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Admin Login</h2>
        <form method="post">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit">Login</button>
            <?php if($error) echo "<div class='error'>$error</div>"; ?>
        </form>
        <div class="footer">© <?php echo date("Y"); ?> Prepsaathi Admin</div>
    </div>
</body>
</html>
