<?php
require_once('../connection/db_connect.php');
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: ../categories/category_list.php");
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
<link rel="stylesheet" href="../css/category_add.css">
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
                <a href="../categories/category_list.php" class="btn btn-outline-secondary w-100 py-1 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
