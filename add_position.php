<?php
session_start();
require_once 'db_connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_name = trim($_POST['position_name']);
    $status = intval($_POST['status']);

    if ($position_name) {
        $stmt = $conn->prepare("INSERT INTO positions (position_name, status) VALUES (?, ?)");
        $stmt->bind_param("si", $position_name, $status);
        if ($stmt->execute()) {
            $message = "Position added successfully.";
            header("Location: position_list.php");
            exit;
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Position name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Position</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Add New Position</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <input type="text" name="position_name" id="position_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Add Position</button>
        <a href="position_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
