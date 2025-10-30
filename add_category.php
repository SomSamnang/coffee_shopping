<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $stmt->close();
            // Redirect immediately to category list after successful insert
            header("Location: category_list.php"); // or category_list.php
            exit();
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    } else {
        $message = "⚠️ Please enter a category name.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f7f8fa; font-family: 'Poppins', sans-serif; }
        .card { border: none; border-radius: 16px; }
        .card-header {
            background: linear-gradient(90deg, #6f42c1, #0d6efd);
            color: white; text-align: center; font-weight: 600; padding: 18px 10px;
            border-top-left-radius: 16px; border-top-right-radius: 16px;
        }
        .card-body { padding: 30px; }
        .form-label { font-weight: 600; }
        .btn-primary { background-color: #0d6efd; border: none; border-radius: 8px; font-weight: 500; }
        .btn-primary:hover { background-color: #0b5ed7; }
        .btn-outline-secondary { border-radius: 8px; }
        .container { max-width: 480px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header">
            🏷️Add New Category
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-info text-center"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Please write here..." required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mt-2">💾 Save Category</button>
                <a href="index.php" class="btn btn-outline-secondary w-100 py-2 mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
