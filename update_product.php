<?php
session_start();
require_once 'db_connect.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) die("Invalid Product ID");

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

$error_msg = '';
$show_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $status = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category_id=?, status=? WHERE product_id=?");
    $stmt->bind_param("sdisi", $name, $price, $category_id, $status, $product_id);

    if ($stmt->execute()) {
        $show_success = true;
    } else {
        $error_msg = "Error updating product: " . $conn->error;
    }
}

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f4f6f8;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
    max-width: 500px;
    margin: 80px auto;
}

.card {
    border-radius: 16px;
    padding: 40px 30px;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-3px);
}

h2 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    color: #1e1e2f;
}

.form-control, .form-select {
    border-radius: 12px;
    height: 50px;
    font-size: 0.95rem;
    padding: 0 15px;
    border: 1px solid #d1d5db;
    transition: all 0.2s;
}
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 8px rgba(13,110,253,0.2);
}

.btn-primary {
    border-radius: 12px;
    height: 50px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-primary:hover {
    background-color: #0b5ed7;
}

.btn-secondary {
    border-radius: 12px;
    height: 50px;
    font-weight: 600;
    background-color: #f3f4f6;
    color: #495057;
}
.btn-secondary:hover {
    background-color: #e2e6ea;
}

.alert {
    border-radius: 12px;
    font-size: 0.95rem;
}
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Update Product</h2>

        <?php if($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <?php if($show_success): ?>
            <div class="alert alert-success">Product updated successfully!</div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-4">
                <label class="form-label" for="name">Product Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="category">Category</label>
                <select name="category_id" id="category" class="form-select" required>
                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-5">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary flex-fill">Update</button>
                <a href="product.php" class="btn btn-secondary flex-fill">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if($show_success): ?>
<script>
    setTimeout(() => window.location.href = "product.php", 1000);
</script>
<?php endif; ?>

</body>
</html>
