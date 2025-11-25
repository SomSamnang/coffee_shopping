<?php
// Include database connection
require_once('../connection/db_connect.php');

$bootstrap_cdn = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"; 

$message = "";
$success = false;

// Fetch active categories
$active_categories = $conn->query("SELECT * FROM categories WHERE status='active' ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Safely get POST data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'inactive';
    
    // Handle image upload
    $image_name = null;
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $original_name = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);

        // Validate image type
        if (in_array($ext, $allowed_extensions)) {
            // Sanitize filename (remove spaces/special chars)
            $safe_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", $original_name);
            $image_name = $safe_name . "_" . time() . "." . $ext;

            // Ensure upload folder exists
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_name);
        } else {
            $message = "⚠️ Invalid image format. Only JPG, JPEG, and PNG allowed.";
        }
    }

    // Validate input fields
    if ($message === "" && $name !== '' && $category_id > 0 && $price >= 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, description, image, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsss", $name, $category_id, $price, $description, $image_name, $status);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $message = "❌ Database error: " . $stmt->error;
        }
        $stmt->close();
    } else if ($message === "") {
        $message = "⚠️ Please fill in all required fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<link rel="stylesheet" href="<?= $bootstrap_cdn ?>">
<link rel="stylesheet" href="../css/add_product.css">
</head>
<body>

<!-- Loading overlay -->
<div id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Add Product</h4>
        </div>
        <div class="card-body p-3">

            <?php if ($message): ?>
                <div class="alert alert-danger py-1"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>✅ Product added successfully! Redirecting...</p>
                </div>
                <script>
                    setTimeout(function(){
                        window.location.href = "..products/product.php";
                    }, 1500);
                </script>
            <?php else: ?>
            
            <form id="addProductForm" method="POST" enctype="multipart/form-data">
                <div class="mb-2">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Product name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php if($active_categories && $active_categories->num_rows > 0): ?>
                            <?php while($cat = $active_categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="">No active categories</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Price (USD) *</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control" placeholder="0.00" required>
                </div>

                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image (JPG, JPEG, PNG)</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png" onchange="previewImage(event)">
                    <img id="imagePreview" class="preview d-none" alt="Preview">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-1">Submit</button>
                <a href="../products/product.php" class="btn btn-outline-secondary w-100 mt-2 py-1">Back</a>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Show loading overlay when form is submitted
document.getElementById('addProductForm')?.addEventListener('submit', function(){
    document.getElementById('loadingOverlay').style.display = 'block';
});

// Image preview
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const file = event.target.files[0];
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    }
}
</script>

</body>
</html>
