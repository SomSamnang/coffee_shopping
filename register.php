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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Page background */
body {
    background: linear-gradient(135deg, #74ebd5, #ACB6E5);
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    flex-direction: column;
}

/* Card */
.card-register {
    background: #fff;
    border-radius: 25px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    padding: 50px 40px;
    width: 100%;
    max-width: 400px;
    text-align: center;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.card-register:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
}

/* Heading */
.card-register h2 {
    font-weight: 700;
    font-size: 2rem;
    background: linear-gradient(90deg,#0d6efd,#6610f2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
}

/* Input Groups */
.input-group-text {
    background-color: #f1f3f5;
    border: none;
    transition: 0.3s;
    font-size: 1.2rem;
}
.input-group-text.username-icon { color: #0d6efd; }
.input-group-text.password-icon { color: #6610f2; }
.input-group-text.role-icon { color: #0dcaf0; }
.input-group-text.status-icon { color: #198754; }

.input-group-text:hover { color: #ff4d6d; }

.form-control, .form-select {
    border-radius: 12px;
    border: 1px solid #ced4da;
    transition: 0.3s;
}
.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    border-color: #0d6efd;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(90deg,#0d6efd,#6610f2);
    border: none;
    font-weight: 600;
    border-radius: 50px;
    padding: 12px 0;
    transition: 0.3s;
}
.btn-primary i { margin-right: 5px; }
.btn-primary:hover { background: linear-gradient(90deg,#6610f2,#0d6efd); }

.btn-link { color: #0d6efd; text-decoration: none; }
.btn-link i { margin-right: 5px; }
.btn-link:hover { text-decoration: underline; }

/* Password toggle */
.password-toggle { cursor: pointer; color: #6610f2; }
.password-toggle:hover { color: #0d6efd; }

/* Alerts */
.alert { text-align: left; font-size: 0.9rem; border-radius: 10px; }

/* Footer */
.footer { margin-top: 20px; font-size: 0.85rem; color: #70707078; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); }

/* Responsive */
@media (max-width: 500px) {
    .card-register { padding: 35px 20px; }
    .card-register h2 { font-size: 1.6rem; }
}
</style>
</head>
<body>

<div class="card-register">
    <h2><i class="bi bi-person-plus-fill"></i> Create Account</h2>
    <?= $message ?>
    <form method="POST">
        <div class="mb-3 input-group">
            <span class="input-group-text username-icon"><i class="bi bi-person-fill"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text password-icon"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" id="passwordInput" required>
            <span class="input-group-text password-toggle" id="togglePassword"><i class="bi bi-eye-fill"></i></span>
        </div>
        <div class="mb-3 input-group">
            <span class="input-group-text role-icon"><i class="bi bi-person-badge-fill"></i></span>
            <select name="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="user">Normal User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="mb-4 input-group">
            <span class="input-group-text status-icon"><i class="bi bi-toggle-on"></i></span>
            <select name="status" class="form-select" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button class="btn btn-primary w-100 mb-3"><i class="bi bi-person-plus-fill"></i> Register</button>
        <a href="login.php" class="btn btn-link"><i class="bi bi-box-arrow-in-right"></i> Already have an account? Login</a>
        <div class="footer"> &copy; 2025 Coffee Shop. All rights reserved.</div>
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

<?php $conn->close(); ?>
