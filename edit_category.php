<?php
require_once 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("Invalid category ID.");

$message = "";

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) die("Category not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $status = $_POST['status'];

    if (empty($name)) {
        $message = "Category name cannot be empty.";
    } else {
        $update = $conn->prepare("UPDATE categories SET name = ?, status = ? WHERE id = ?");
        $update->bind_param("ssi", $name, $status, $id);
        if ($update->execute()) {
            header("Location: category_list.php?msg=Category updated successfully");
            exit;
        } else {
            $message = "Error updating category: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>/* Body & Centering */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f9fafb; /* softer background */
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

/* Card */
.form-card {
    width: 100%;
    max-width: 420px; /* slightly smaller for a cleaner look */
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* softer shadow */
}

/* Header */
.form-header {
     background: linear-gradient(90deg, #6f42c1, #0d6efd);/* single solid color, cleaner */
    color: #fff;
    text-align: center;
    font-size: 20px;
    font-weight: 600;
    padding: 18px 0;
    letter-spacing: 0.5px;
}

/* Body padding */
.form-body {
    padding: 28px 30px;
}

/* Inputs & Selects */
.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 10px 14px;
    font-size: 15px;
    margin-bottom: 16px;
    transition: all 0.3s;
}

.form-control:focus, .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79,70,229,0.2); /* lighter focus effect */
    outline: none;
}

/* Buttons */
.btn-submit {
    width: 100%;
    background: #4f46e5;
    color: #fff;
    font-weight: 500;
    border: none;
    padding: 10px 0;
    border-radius: 6px;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.btn-submit:hover {
    background: #6366f1;
}

.btn-back {
    width: 100%;
    background: #e5e7eb;
    color: #374151;
    font-weight: 500;
    border: none;
    padding: 10px 0;
    border-radius: 6px;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #d1d5db;
}

/* Error message */
.message {
    text-align: center;
    color: #ef4444;
    margin-bottom: 15px;
    font-weight: 500;
}

/* Labels */
.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}

</style>
</head>
<body>

<div class="form-card">
    <div class="form-header">Edit Category</div>

    <div class="form-body">
        <?php if($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="name" class="form-label">Category Name *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>

            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button type="submit" class="btn btn-submit">Update</button>
            <a href="category_list.php" class="btn btn-back">Back</a>
        </form>
    </div>
</div>

</body>
</html>
