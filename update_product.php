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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    background: linear-gradient(135deg, #f0f4ff, #ffffff);
    font-family: 'Inter', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
}

.container {
    max-width: 450px;
    width: 100%;
    padding: 25px;
}

.card {
    background: #ffffff;
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    font-weight: 700;
    background: linear-gradient(90deg, #4f46e5, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
}

.form-label {
    font-weight: 500;
    color: #475569;
    margin-bottom: 6px;
}

.form-control, .form-select {
    border-radius: 14px;
    height: 50px;
    font-size: 0.95rem;
    padding: 0 16px;
    border: 1px solid #d1d5db;
    transition: all 0.25s ease;
}
.form-control:focus, .form-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 10px rgba(99,102,241,0.15);
    outline: none;
}

.mb-4 { margin-bottom: 20px !important; }
.mb-5 { margin-bottom: 28px !important; }

.btn-primary {
    border-radius: 14px;
    height: 50px;
    font-weight: 600;
    background: linear-gradient(90deg, #4f46e5, #6366f1);
    border: none;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background: linear-gradient(90deg, #6366f1, #4f46e5);
}

.btn-secondary {
    border-radius: 14px;
    height: 50px;
    font-weight: 600;
    background-color: #f3f4f6;
    color: #475569;
    border: none;
}
.btn-secondary:hover {
    background-color: #e5e7eb;
}

.alert {
    border-radius: 14px;
    font-size: 0.95rem;
    padding: 12px 18px;
    margin-bottom: 20px;
}

@media(max-width: 576px) {
    .container { padding: 20px; }
    .card { padding: 30px 25px; }
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
    setTimeout(() => window.location.href = "product.php", 1200);
</script>
<?php endif; ?>

</body>
</html>
