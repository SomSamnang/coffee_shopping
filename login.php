<?php
session_start();
require_once 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "index.php" : "orders.php"));
    exit;
}

$error = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "⚠️ Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $error = "⛔ You can't login to the system.<br> Please try again later.";
            } else if (password_verify($password, $user['password'])) {
                // Login success
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id'];
                $login_success = true;
            } else {
                $error = "❌ Incorrect password.";
            }

        } else {
            $error = "❌ Username not found.";
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
<link rel="stylesheet" href="style/login.css">
</head>
<body>
<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner-border text-warning"></div>
    <p>Please wait…</p>
</div>

<div class="login-card">

    <h3>☕ Relax Coffee Login</h3>

    <?php if ($error): ?>
        <div class="cool-alert" id="alertBox">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3 input-icon">
            <i class="fas fa-user"></i>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="mb-3 input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
        </div>

        <button type="submit" class="btn btn-custom w-100">Login</button>

        <a href="login.php" class="btn w-100 mt-2" 
           style="background: linear-gradient(90deg, #ff7e5f, #feb47b); color: #fff; border-radius:12px; padding:12px; font-weight:500;">
            Cancel
        </a>
    </form>

    <div class="footer">
        &copy; 2025 Coffee Shop. All rights reserved.
    </div>

</div>

<script>
function togglePassword() {
    const field = document.getElementById('password');
    const icon = document.querySelector('.toggle-password');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Hide alert automatically after 3 seconds
const alertBox = document.getElementById('alertBox');
if (alertBox) {
    setTimeout(() => { alertBox.style.display = 'none'; }, 3000);
}

// Show loading overlay if login successful
<?php if ($login_success): ?>
document.getElementById("loadingOverlay").style.display = "flex";
setTimeout(() => {
    window.location.href = "<?= ($_SESSION['role'] === 'admin') ? 'index.php' : 'orders.php' ?>";
}, 1200);
<?php endif; ?>
</script>

</body>
</html>
<?php $conn->close(); ?>
