<?php
session_start();
require_once 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle Add/Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_modal'])) {
    $id = intval($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? 'active';

    $message = '';
    $success = false;

    if ($username && $role && $status) {
        if ($id > 0) {
            // Update user
            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=?, status=? WHERE id=?");
                $stmt->bind_param("ssssi", $username, $hashed, $role, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, role=?, status=? WHERE id=?");
                $stmt->bind_param("sssi", $username, $role, $status, $id);
            }
            $success = $stmt->execute();
            $stmt->close();
            $message = $success ? "User updated successfully." : "Failed to update user.";
        } else {
            // Add new user
            $check = $conn->prepare("SELECT id FROM users WHERE username=?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $message = "Username already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $hashed, $role, $status);
                $success = $stmt->execute();
                $stmt->close();
                $message = $success ? "User added successfully." : "Failed to add user.";
            }
            $check->close();
        }
    } else {
        $message = "Please fill all required fields.";
    }

    // Pass message to JS for overlay
    $_SESSION['user_message'] = $message;
    $_SESSION['user_success'] = $success ? 1 : 0;

    header("Location: user_list.php"); // Redirect to same page to prevent resubmission
    exit;
}

// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

// Get message if exists
$user_message = $_SESSION['user_message'] ?? '';
$user_success = $_SESSION['user_success'] ?? 0;
unset($_SESSION['user_message'], $_SESSION['user_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #eef2f7; }
#loadingOverlay {
    display: none;
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.4);
    justify-content: center; align-items: center; z-index:9999;
}
#loadingOverlay .spinner-border { width: 3rem; height: 3rem; }
</style>
</head>
<body>
<div id="loadingOverlay" class="d-flex">
    <div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div>
</div>

<div class="container mt-4">
    <h3 class="mb-3">Users List</h3>
    <table class="table table-bordered">
        <thead>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php while($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['role'] ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add/Edit User Form (simplified for example) -->
    <form method="POST" id="userForm">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="user_modal" value="1">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password">
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <select name="status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <button type="submit" class="btn btn-success mt-2">Save</button>
    </form>
</div>

<script>
const userForm = document.getElementById('userForm');
const overlay = document.getElementById('loadingOverlay');

// Show overlay when submitting add/update
userForm.addEventListener('submit', function() {
    overlay.style.display = 'flex';
});

// Show overlay and auto redirect if success
<?php if($user_message): ?>
    overlay.style.display = 'flex';
    setTimeout(() => {
        alert("<?= htmlspecialchars($user_message) ?>");
        window.location.href = 'user_list.php'; // Redirect to user list
    }, 500); // 0.5s delay for effect
<?php endif; ?>
</script>

</body>
</html>
