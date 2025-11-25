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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 500px;
    background: #fff;
}

h2 {
    text-align: center;
    font-weight: 700;
    color: #1100ff;
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 500;
}

.btn-gradient {
    background: linear-gradient(45deg, #ff7e5f, #feb47b);
    border: none;
    color: #fff;
    font-weight: 600;
    transition: 0.3s;
}

.btn-gradient:hover {
    transform: scale(1.05);
}

.alert {
    border-radius: 10px;
}

.back-btn {
    background: linear-gradient(45deg, #6a11cb, #2575fc);
    color: #fff;
    border: none;
    transition: 0.3s;
}

.back-btn:hover {
    transform: scale(1.05);
}
</style>
</head>
<body>

<div class="card">
    <h2><i class="bi bi-briefcase me-2"></i>Add New Position</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clipboard-plus"></i></span>
                <input type="text" name="position_name" id="position_name" class="form-control" placeholder="Enter position name" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-gradient"><i class="bi bi-check-lg me-1"></i>Add Position</button>
            <a href="position_list.php" class="btn back-btn"><i class="bi bi-arrow-left-circle me-1"></i>Back</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
