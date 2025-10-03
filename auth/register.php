<?php
session_start();
include 'connect.php';

// ---------- REGISTER ----------
if (isset($_POST['signUp'])) {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$checkEmail) {
        die("Prepare failed: " . $conn->error);
    }
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        echo "Email Address Already Exists!";
    } else {
        // Insert new user (default role = 'user')
        $insertQuery = $conn->prepare(
            "INSERT INTO users (first_name, last_name, email, password, role)
VALUES (?, ?, ?, ?, 'user')"
        );
        if (!$insertQuery) {
            die("Prepare failed: " . $conn->error);
        }
        $insertQuery->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

        if ($insertQuery->execute()) {
            header("Location: /index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
    $checkEmail->close();
}

// ---------- LOGIN ----------
if (isset($_POST['signIn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ? LIMIT 1");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] === 'admin') {
                header("Location: /Admin/index.php");
            } else {
                header("Location: /index.php");
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }
    $sql->close();
}
