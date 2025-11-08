<?php
session_start();
require_once('db_connect.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';

    if ($username && $password && $role) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Username already exists!</div>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed, $role, $status);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>User registered successfully! <a href='login.php'>Login here</a></div>";
            } else {
                $message = "<div class='alert alert-danger'>Failed to register user.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill all fields.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #74ebd5, #ACB6E5); font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.card-register { background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 30px; width: 100%; max-width: 400px; text-align: center; }
.input-group-text { background-color: #e9ecef; border: none; }
.form-control:focus, .form-select:focus { box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25); border-color: #0d6efd; }
.btn-primary { background-color: #0d6efd; border: none; transition: 0.3s; }
.btn-primary:hover { background-color: #0b5ed7; }
.password-toggle { cursor: pointer; }
</style>
</head>
<body>
<div class="card-register">
    <h2>Create Account</h2>
    <?= $message ?>
    <form method="POST">
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" id="passwordInput" required>
            <span class="input-group-text password-toggle" id="togglePassword"><i class="bi bi-eye-fill"></i></span>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
            <select name="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="user">Normal User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
            <select name="status" class="form-select" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button class="btn btn-primary w-100 mb-2">Register</button>
        <a href="login.php" class="btn btn-link">Already have an account? Login</a>
    </form>
</div>
<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('passwordInput');
togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    togglePassword.innerHTML = type === 'password' ? '<i class="bi bi-eye-fill"></i>' : '<i class="bi bi-eye-slash-fill"></i>';
});
</script>
</body>
</html>
