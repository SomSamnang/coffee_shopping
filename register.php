<?php
session_start();
require_once('db_connect.php');

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';

    if ($username && $email && $password && $role) {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Username already exists!</div>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, display_password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $hashed, $role, $status, $password);

            if ($stmt->execute()) {
                $success = true;
                
            } else {
                $message = "<div class='alert alert-danger'>Failed to register user.</div>";
            }
        }
        $stmt->close();
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
<link rel="stylesheet" href="style/register.css">

</head>
<body>

<div id="successOverlay">
    <div class="box">
        <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;"></div>
        <h5 class="text-success fw-bold">Registering successfully...!</h5>
        <p class="text-secondary">Please wait...</p>
    </div>
</div>

<div class="card-register">
    <h2><i class="bi bi-person-plus-fill"></i> Create Account</h2>
    <?= $message ?>

    <form method="POST" id="registerForm">

        <!-- Username -->
        <div class="mb-3 input-group">
            <span class="input-group-text input-icon-username"><i class="bi bi-person-fill"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <!-- Password -->
        <div class="mb-3 input-group">
            <span class="input-group-text input-icon-password "><i class="bi bi-lock-fill input-icon-bi-eye"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <span class="input-group-text " id="togglePassword"><i class="bi bi-eye-fill"style="color:#6f42c1;"></i></span>
        </div>

        <!-- Email -->
        <div class="mb-3 input-group">
            <span class="input-group-text input-icon-email"><i class="bi bi-envelope-fill"></i></span>
            <input type="text" id="emailInput" name="email" class="form-control" placeholder="@cfe.shopping.com" required>
        </div>

        <!-- Role -->
        <div class="mb-3 input-group">
            <span class="input-group-text input-icon-role "><i class="bi bi-person-badge-fill "></i></span>
            <select name="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <!-- Status -->
        <div class="mb-4 input-group">
            <span class="input-group-text input-icon-status"><i class="bi bi-toggle-on"></i></span>
            <select name="status" class="form-select" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-person-plus-fill me-2"></i> Register
        </button>

        <a href="user_list.php" class="btn w-100 mb-3" style="background: linear-gradient(90deg,yellow,green); color:#fff;">
            <i class="bi bi-arrow-left-circle-fill me-2"></i> Back
        </a>

        <div class="footer text-muted">&copy; 2025 Coffee Shop. All rights reserved.</div>
    </form>
</div>

<script>
// Password show/hide
document.getElementById('togglePassword').onclick = function () {
    const input = document.querySelector('input[name="password"]');
    const type = input.type === "password" ? "text" : "password";
    input.type = type;
    this.innerHTML = type === "password" ? '<i class="bi bi-eye-fill"></i>' : '<i class="bi bi-eye-slash-fill"></i>';
};

// Auto append email domain
const emailInput = document.getElementById('emailInput');
const domain = '@cfe.shopping.com';
emailInput.addEventListener('input', () => {
    let value = emailInput.value.replace(domain,'');
    emailInput.value = value + domain;
    emailInput.setSelectionRange(value.length, value.length);
});

// Show loading overlay on form submit
const form = document.getElementById('registerForm');
const overlay = document.getElementById('successOverlay');

form.addEventListener('submit', function(e) {
    overlay.style.display = "flex";
});
<?php if ($success): ?>
overlay.style.display = "flex";
setTimeout(() => { window.location.href = "user_list.php"; }, 1000);
<?php endif; ?>


</script>

</body>
</html>

<?php $conn->close(); ?>
