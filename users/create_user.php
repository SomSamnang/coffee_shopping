<?php
session_start();

require_once('../connection/db_connect.php');

// Only admin can access this page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    $status = $_POST['status'];

    if (empty($username) || empty($password)) {
        $error = "Username and password are required!";
    } else {
        // Check if username already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $role, $status);

            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "User '$username' created successfully!";
                header("Location: ../users/user_list.php"); // ✅ make sure user_list.php is in same folder
                exit;
            } else {
                $error = "Failed to create user. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="..css/create_user.css">
</head>
<body>
<div class="container">
    <div class="card p-4">
        <h3 class="text-center mb-3">➕ Create New User</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" required placeholder="Enter username">
            </div>

            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter password">
            </div>

            <div class="mb-3">
                <label class="form-label">Role:</label>
                <select name="role" class="form-select">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Status:</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="../users/user_list.php" class="btn btn-secondary">⬅ Back</a>
                <button type="submit" class="btn btn-custom">Create</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
