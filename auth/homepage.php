<?php
include("connect.php");
include("../partials/header.php");

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PrepSaathi - Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fbfd;
            color: #2c3e50;
            line-height: 1.5;
        }

        .container {
            max-width: 900px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 12px;
            padding: 2rem 2.5rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        header.header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        header.header h1 {
            font-weight: 700;
            font-size: 2rem;
            color: #34495e;
        }

        .btn-logout {
            background-color: #e74c3c;
            border: none;
            color: white;
            padding: 0.6rem 1.25rem;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #c0392b;
        }

        form.profile-form {
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.4rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            color: #34495e;
            cursor: pointer;
            font-size: 1rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.6rem 0.9rem;
            font-size: 1rem;
            border-radius: 8px;
            border: 1.8px solid #bdc3c7;
            transition: border-color 0.3s ease, box-shadow 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 6px rgba(52, 152, 219, 0.4);
        }

        input::placeholder {
            color: #95a5a6;
        }

        .btn-submit {
            background-color: #3498db;
            color: white;
            padding: 0.75rem 1.8rem;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #2980b9;
        }


        .alert {
            padding: 1rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }


        @media (max-width: 640px) {
            .container {
                margin: 1.5rem 1rem;
                padding: 1.5rem 1.5rem;
            }

            header.header h1 {
                font-size: 1.5rem;
            }

            .btn-logout {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .btn-submit {
                font-size: 1rem;
                padding: 0.65rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container" role="main">
        <header class="header">
            <h1>Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
        </header>
        <section aria-labelledby="profileHeading" tabindex="0">

            <form class="profile-form" id="profileForm" onsubmit="updateProfile(event)" novalidate>
                <div class="form-group">
                    <label class="form-label" for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName"
                        value="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"
                        placeholder="Your full name" required aria-required="true" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($user['email']); ?>"
                        placeholder="you@example.com" required aria-required="true" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">New Password <small></small></label>
                    <input type="password" id="password" name="password" placeholder="Enter new password" autocomplete="off" />
                </div>
                <button type="submit" class="btn-submit">Update Profile</button>
            </form>
        </section>
    </div>
    <script>
        async function showAlert(message, type) {
            let existingAlert = document.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            let alertBox = document.createElement('div');
            alertBox.textContent = message;
            alertBox.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-info');
            const container = document.querySelector('.container');
            container.insertBefore(alertBox, container.firstChild);
            setTimeout(() => alertBox.remove(), 4000);
        }

        async function updateProfile(event) {
            event.preventDefault();
            const form = event.target;
            const fullName = form.fullName.value.trim();
            const email = form.email.value.trim();
            const password = form.password.value;
            if (!fullName || !email) {
                return showAlert('Please fill in all required fields.', 'info');
            }
            try {
                const response = await fetch('update-profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        fullName,
                        email,
                        password
                    })
                });
                const result = await response.json();
                if (result.status === "success") {
                    showAlert('Profile updated successfully!', 'success');
                } else {
                    showAlert('Profile update failed. Please try again.', 'info');
                }
                form.password.value = '';
            } catch (error) {
                showAlert('Error connecting to server.', 'info');
            }
        }
    </script>
    <?php include("../partials/footer.php"); ?>
</body>

</html>