<?php

session_start();
require_once('../connection/db_connect.php');



if (isset($_SESSION['username'])) {
    $role = $_SESSION['role'] ?? '';
header("Location: " . ($role === 'admin' ? "../home/index.php" : "../orders/orders.php"));
exit();


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
<link rel="stylesheet" href="../css/login.css">
</head>
<body>

<div id="loadingOverlay">
    <div class="spinner-border text-warning"></div>
    <p>Logging in…</p>
</div>

<div class="login-card">

    <h3>☕ Relax Coffee Login</h3>

    <?php if ($error): ?>
        <div class="cool-alert" id="alertBox"><?= $error ?></div>
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
        <a href="../users/login.php" class="btn w-100 mt-2" style="background: linear-gradient(90deg, #6a11cb, #2575fc); color:#fff; border-radius:12px; padding:12px;">Cancel</a>
    </form>

    <div class="footer mt-3">&copy; 2025 Coffee Shop. All rights reserved.</div>

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

const alertBox = document.getElementById('alertBox');
if (alertBox) setTimeout(() => { alertBox.style.display = 'none'; }, 3000);

<?php if ($login_success): ?>
document.getElementById("loadingOverlay").style.display = "flex";
setTimeout(() => {
    window.location.href = "<?= ($_SESSION['role'] === 'admin') ? '../home/index.php' : '../orders/orders.php' ?>";
}, 1200);
<?php endif; ?>
</script>

</body>
</html>
<?php $conn->close(); ?>
