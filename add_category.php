<?php
include 'db_connect.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: category_list.php");
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
    body {
        background-color: #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        background: linear-gradient(90deg, #6f42c1, #0d6efd);
        color: white;
        text-align: center;
        font-weight: 600;
        padding: 14px 10px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        font-size: 1.1rem;
    }
    .card-body {
        padding: 20px;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.95rem;
    }
    .form-control {
        padding: 6px 10px;
        font-size: 0.9rem;
    }
    .btn-primary {
        background-color: #0d6efd;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.95rem;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
    }
    .btn-outline-secondary {
        border-radius: 6px;
        font-size: 0.95rem;
    }
    .container {
        max-width: 400px; /* make form smaller */
    }
    /* Hide scrollbar but allow scroll */
html, body {
    height: 100%;
    overflow: auto;
    scrollbar-width: none;
}
body::-webkit-scrollbar {
    display: none;
}
</style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header">
            🏷️ Add New Category
        </div>
        <div class="card-body">
            <?php if(!empty($message)): ?>
                <div class="alert alert-info py-1 text-center"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-2">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter category name" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-1 mt-2">💾 Save</button>
                <a href="index.php" class="btn btn-outline-secondary w-100 py-1 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
