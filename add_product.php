<?php
// Include database connection
include 'db_connect.php'; 
$bootstrap_cdn = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"; 

$message = "";

// ✅ Load categories from DB
$categories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];

    if (!empty($name) && is_numeric($price) && $price >= 0 && !empty($category_id)) {
        $stmt = $conn->prepare("INSERT INTO products (name, category_id, price) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $name, $category_id, $price);
        if ($stmt->execute()) {
            header("Location: product.php?message=Product+added");
            exit;
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ Please fill all fields correctly.";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<link rel="stylesheet" href="<?php echo $bootstrap_cdn; ?>">
<style>
    body {
        background-color: #f8f9fa;
        font-family: "Poppins", sans-serif;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        background: linear-gradient(90deg, #6f42c1, #0d6efd);
        color: white;
        text-align: center;
        padding: 14px 10px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
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
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
    }
    .form-control, .form-select {
        padding: 6px 10px;
        font-size: 0.9rem;
    }
    .container {
        max-width: 400px; /* smaller form width */
    }
    .btn-outline-secondary {
        font-size: 0.9rem;
        border-radius: 6px;
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
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">☕ Add Product</h4>
        </div>
        <div class="card-body p-3">

            <?php if($message): ?>
                <div class="alert alert-info py-1"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-2">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Product name" required>
                </div>

                <div class="mb-2">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Price (USD) *</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control" placeholder="0.00" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-1">Submit</button>
                <a href="index.php" class="btn btn-outline-secondary w-100 mt-2 py-1">Back</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>

