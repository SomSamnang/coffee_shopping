<?php
require_once 'db_connect.php';

// Fetch products with category names
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.product_id DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products List</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #f7f8fa;
    font-family: 'Poppins', sans-serif;
    color: #333;
}

header {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    color: white;
    padding: 15px 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    border-bottom: 2px solid rgba(255,255,255,0.2);
}
header h1 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}
header .nav-buttons a {
    margin-left: 8px;
}

.container {
    max-width: 1000px;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
}

.card-body {
    padding: 25px;
}

.table {
    border-radius: 12px;
    overflow: hidden;
}

.table thead th {
    text-align: center;
    background-color: #0d6efd;
    color: #fff;
    font-weight: 600;
    border: none;
}
.table tbody td {
    text-align: center;
    vertical-align: middle;
    background-color: #fff;
}
.table-hover tbody tr:hover {
    background-color: #f1f4ff;
    transition: 0.2s;
}

.btn {
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
}

.btn-edit {
    background-color: #198754;
    color: white;
}
.btn-edit:hover { background-color: #157347; }

.btn-delete {
    background-color: #dc3545;
    color: white;
}
.btn-delete:hover { background-color: #b02a37; }

.btn-add {
    background-color: #0d6efd;
    color: white;
}
.btn-add:hover { background-color: #0b5ed7; }

.no-products {
    text-align: center;
    font-size: 1.1rem;
    color: #6c757d;
    margin: 20px 0;
}

@media (max-width: 768px) {
    header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .nav-buttons { width: 100%; display: flex; justify-content: flex-start; flex-wrap: wrap; gap: 8px; }
}
</style>
</head>

<body>

<header>
    <h1> Product Management</h1>
    <div class="nav-buttons">
        <a href="add_product.php" class="btn btn-light btn-sm">➕ Add Product</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">← Back</a>
    </div>
</header>

<div class="container my-4">
    <div class="card">
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle shadow-sm">
                        <thead>
                            <tr>
                                <th width="70">ID</th>
                                <th>Name</th>
                                <th width="180">Category</th>
                                <th width="100">Price ($)</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_id']); ?></td>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td><?= htmlspecialchars($row['category_name'] ?? 'No Category'); ?></td>
                                <td>$<?= number_format($row['price'], 2); ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="edit_product.php?id=<?= $row['product_id']; ?>" class="btn btn-edit btn-sm">Edit</a>
                                        <a href="delete_product.php?id=<?= $row['product_id']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-products">No products found. Use the “Add Product” button above to create one.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
