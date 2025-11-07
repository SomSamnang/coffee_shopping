<?php
session_start();
require_once 'db_connect.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) die("Invalid Product ID");

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) die("Product not found");

// Fetch categories
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");

$success = false;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $status = $_POST['status'] ?? 'active';
    $description = $_POST['description'] ?? '';

    $image_name = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'product_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_name);
    }

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category_id=?, status=?, description=?, image=? WHERE product_id=?");
    $stmt->bind_param("sdisssi", $name, $price, $category_id, $status, $description, $image_name, $product_id);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error_msg = "Update failed: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f7f8fa; display:flex; justify-content:center; align-items:center; min-height:100vh; }
.card { padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.08); background:white; width:100%; max-width:500px; }
img.preview { width:150px; border-radius:8px; display:block; margin-top:10px; }
.spinner-container { text-align:center; margin-top:20px; }
</style>
</head>
<body>

<div class="card">
    <h2 class="mb-3 text-center">Update Product</h2>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success text-center">
            <div class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <p>Product updated successfully! <span id="countdown">3</span> seconds...</p>
        </div>

        <script>
            // Countdown redirect
            let seconds = 1;
            const countdownEl = document.getElementById('countdown');
            const interval = setInterval(() => {
                seconds--;
                countdownEl.textContent = seconds;
                if(seconds <= 0){
                    clearInterval(interval);
                    window.location.href = "product.php"; // Redirect to product list
                }
            }, 150);
        </script>

    <?php else: ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <?php
                // Reset result pointer
                $categories_result->data_seek(0);
                while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id']==$product['category_id']?'selected':'' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="active" <?= $product['status']=='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $product['status']=='inactive'?'selected':'' ?>>Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control">
            <?php if(!empty($product['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Product Image" class="preview">
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Update</button>
            <a href="product.php" class="btn btn-secondary flex-fill">Cancel</a>
        </div>
    </form>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
