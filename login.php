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

<style>
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(120deg, #ffe0b3, #ffcc80, #ff9933, #ffc266);
    background-size: 400% 400%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: gradientBG 15s ease infinite;
    padding: 20px;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.login-card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    padding: 35px 30px;
    width: 100%;
    max-width: 420px;
    animation: fadeIn .7s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

#loadingOverlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255,255,255,0.9);
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    z-index: 9999;
}
#loadingOverlay .spinner-border {
    width: 4rem;
    height: 4rem;
}
#loadingOverlay p {
    margin-top: 12px;
    font-weight: 600;
    font-size: 1.3rem;
    color: #ff660074;
}

.login-card h3 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 25px;
    color: #fff;
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    padding: 12px 0;
    border-radius: 12px;
    font-size: 1.4rem;
}

.form-control {
    border-radius: 12px;
    padding: 18px 15px 18px 45px;
    font-size: 1rem;
}

.input-icon {
    position: relative;
}
.input-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #777;
}
.toggle-password {
    right: 15px !important;
    left: auto !important;
    cursor: pointer;
}

.btn-custom {
    background: linear-gradient(90deg, #ff9933, #ff8000);
    color: #fff;
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
}

.cool-alert {
    background: #ffe1e1;
    border-left: 4px solid #ff3b3b;
    padding: 10px 15px;
    border-radius: 10px;
    color: #b30000;
    font-size: 0.95rem;
    text-align: center;
    margin-bottom: 18px;
    transition: all 0.5s ease;
}

.footer {
    text-align: center;
    margin-top: 20px;
    font-size: 0.8rem;
    color: #666;
}
</style>
</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner-border text-warning"></div>
    <p>Please wait…</p>
</div>

<div class="login-card">

    <h3>☕ Coffee Shop Login</h3>

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
