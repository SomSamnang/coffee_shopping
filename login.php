<?php
session_start();
require_once 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "index.php" : "orders.php"));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];
                header("Location: " . ($user['role'] === 'admin' ? "index.php" : "orders.php"));
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Username not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Coffee Shop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(120deg, #fef9f0, #ffe0b3);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    /* Animated gradient background */
    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    body {
        background: linear-gradient(120deg, #ffe0b3, #ffcc80, #ff9933, #ffc266);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
    }

    .login-card {
        background: #fff;
        border-radius: 25px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        padding: 30px 20px;
        width: 100%;
        max-width: 410px;
        position: relative;
        animation: slideUp 0.6s ease forwards;
    }

    @keyframes slideUp {
        0% { transform: translateY(50px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

.login-card h3 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    color: #ffffff; /* White text looks clean on colored background */
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    background: linear-gradient(90deg, #0d6efd, #6610f2); /* Soft gradient */
    padding: 10px 0;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}


    .form-control {
        border-radius: 12px;
        padding: 18px 15px 18px 45px;
        font-size: 1rem;
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: #ff9933;
        box-shadow: 0 0 0 0.2rem rgba(255,153,51,0.25);
    }

    .btn-custom {
        background: linear-gradient(90deg, #ff9933, #ff8000);
        color: #fff;
        font-weight: 500;
        border-radius: 12px;
        padding: 12px;
        transition: 0.3s;
    }
    .btn-custom:hover {
        background: linear-gradient(90deg, #ff8000, #ff9933);
    }

    .text-center a {
        color: #001affff;
        font-size: 0.9rem;
        text-decoration: none;
    }
    .text-center a:hover {
        color: #ff9933;
    }

    .alert {
        font-size: 0.9rem;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .input-icon {
        position: relative;
    }
    .input-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 1rem;
        cursor: pointer;
    }
    .input-icon .toggle-password {
        right: 15px;
        left: auto;
    }

    .footer {
        text-align: center;
        margin-top: 25px;
        font-size: 0.8rem;
        color: #888;
    }
</style>
</head>
<body>

<div class="login-card">
    <h3>☕ Coffee Shop Login</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3 input-icon">
            <i class="fas fa-user"></i>
            <input type="text" name="username" class="form-control" required placeholder="Username">
        </div>

        <div class="mb-3 input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" class="form-control" required placeholder="Password">
            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
        </div>

        <button type="submit" class="btn btn-custom w-100">Login</button>
    </form>

    <div class="text-center mt-4">
        <a href="register.php">Create an account</a>
    </div>

    <div class="footer">
        &copy; 2025 Coffee Shop. All rights reserved.
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.querySelector('.toggle-password');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html> 
