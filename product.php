<?php
require_once 'db_connect.php';
// Fetch only active products (hide inactive)
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category_name, p.status
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'  -- only active products
        ORDER BY p.product_id DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
// Fetch products with category names and status
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category_name, p.status
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background-color: #f7f8fa;
    font-family: 'Poppins', sans-serif;
    color: #333;
    margin: 0;
}

/* Header */
header {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    color: white;
    padding:9px 18px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    border-bottom: 2px solid rgba(255,255,255,0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
header h1 {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    background: linear-gradient(90deg, #ffffff, #fdd10d);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Buttons and Search Container */
.nav-buttons a {
    margin-left: 8px;
}
.search-container {
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-radius: 12px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 250px;
}
.search-container input {
    border: none;
    outline: none;
    flex: 1;
    font-size: 0.95rem;
}
.search-container i {
    color: #0d6efd;
}

/* Container */
.container {
    max-width: 1000px;
    margin: 30px auto;
}

/* Card */
.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}
.card-body {
    padding: 25px;
}

/* Table */
.table {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #dee2e6;
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

/* Buttons */
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
.btn-toggle {
    font-size: 0.75rem;
}

/* No products message */
.no-products {
    text-align: center;
    font-size: 1.1rem;
    color: #6c757d;
    margin: 20px 0;
}

/* Status badges */
.badge-active {
    background-color: #198754;
    color: #fff;
}
.badge-inactive {
    background-color: #6c757d;
    color: #fff;
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

@media (max-width:768px) {
  .product-card { padding:15px; }
  .category-nav { overflow-x:auto; white-space:nowrap; padding-bottom:5px; }
  .category-nav::-webkit-scrollbar { display:none; }
}
</style>
</head>
<body>

<header>
    <h1> Product Menu</h1>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="search-container">
            <input type="text" id="searchBox" placeholder="Search products..."><i class="bi bi-search"></i>
        </div>
        <div class="nav-buttons d-flex gap-1">
            <a href="add_product.php" class="btn btn-add btn-sm">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</header>

<div class="container">
    <div class="card">
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle shadow-sm" id="productTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price ($)</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                    <?php if($row['status']=='active'): ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="update_product.php?id=<?= $row['product_id']; ?>" class="btn btn-edit btn-sm">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                       
                                        <a href="delete_product.php?id=<?= $row['product_id']; ?>" class="btn btn-delete btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
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

<!--  Live Search -->
<script>
document.getElementById("searchBox").addEventListener("keyup", function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll("#productTable tbody tr");
    rows.forEach(row => {
        const name = row.cells[1].innerText.toLowerCase();
        const category = row.cells[2].innerText.toLowerCase();
        row.style.display = (name.includes(query) || category.includes(query)) ? "" : "none";
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>
