<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check empty fields
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger'>All fields are required!</div>";
    } else if ($new_password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>New password and confirm password do not match!</div>";
    } else {
        // Fetch old password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify old password
        if (!password_verify($old_password, $user['password'])) {
            $message = "<div class='alert alert-danger'>Old password is incorrect!</div>";
        } else {
            // Update new password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $user_id);
            $update->execute();

            $message = "<div class='alert alert-success'>Password changed successfully!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef1f5;
            font-family: "Inter", sans-serif;
        }
        .card {
            max-width: 450px;
            margin: 60px auto;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>

<div class="card p-4 bg-white">
    <h3 class="text-center mb-3">Change Password</h3>

    <?= $message ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Old Password</label>
            <input type="password" name="old_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Save New Password</button>

        <div class="text-center mt-3">
            <a href="../my_profile/my_profile.php" class="text-decoration-none">Back to Profile</a>
        </div>
    </form>
</div>

</body>
</html>
