<?php
require_once 'db_connect.php';
session_start();

// Fetch logged-in user info
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// Fetch products including nullable description and image
$sql = "SELECT p.product_id, p.name, p.price, c.name AS category_name, p.status, p.description, p.image
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
<link rel="stylesheet" href="style/product.css">
</head>
<body>

<header>
<h1>Product Menu</h1>
<div class="d-flex align-items-center gap-2 flex-wrap">

    <!-- Search -->
    <div class="search-container">
        <input type="text" id="searchBox" placeholder="Search products...">
        <i class="bi bi-search"></i>
    </div>


   <!-- User Dropdown / Login -->
<ul class="nav">
<?php if($currentUser): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="color:white;">
            <i class="bi bi-person-circle me-1" style="color:yellow;"></i>
            <?= htmlspecialchars($currentUser) ?>
        </a>

        <ul class="dropdown-menu dropdown-menu-end">
            <!-- Profile -->
            <li>
                <a class="dropdown-item" href="profile.php" style="color:blue; font-weight:500;">
                    <i class="bi bi-person me-2" style="color:blue;"></i> Profile
                </a>
            </li>
                    <!-- Category -->
            <li>
                <a class="dropdown-item" href="category_list.php" style="color:blue; font-weight:500;">
                    <i class="bi bi-list-ul me-2" style="color:blue;"></i> Category
                </a>
            </li>


            <!-- Home -->
<li>
    <a class="dropdown-item d-flex align-items-center gap-2" href="index.php" style="color:blue; style="font-weight:500;">
       
            <i class="bi bi-house-door "style="color:pink;"></i>
        </span>
        Home
    </a>
</li>


            <!-- Users (admin only) -->
            <?php if($role === 'admin'): ?>
            <li>
                <a class="dropdown-item" href="user_list.php"style="color:blue; font-weight:500;">
                    <i class="bi bi-people-fill me-2"style="color:green;"></i> Users
                </a>
            </li>
            <?php endif; ?>

            <li><hr class="dropdown-divider"></li>

            <!-- Logout -->
            <li>
                <a class="dropdown-item text-danger" href="logout.php" style="color:blue;font-weight:500;">
                    <i class="bi bi-box-arrow-right me-2" style="color:red;"></i> Logout
                </a>
            </li>
        </ul>
    </li>
<?php else: ?>
    <li class="nav-item">
        <a class="nav-link btn btn-outline-light btn-sm" href="login.php" style="color:blue; font-weight:500;">
            <i class="bi bi-box-arrow-in-right me-2" style="color:green;"></i> Login
        </a>
    </li>
<?php endif; ?>
</ul>


</div>
</header>

<div class="container">
<div class="card">
    
<div class="card-body">
        <!-- Nav Buttons -->
      <div class="nav-buttons d-flex gap-1 mb-3">
        <a href="add_product.php" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Add Product
        </a>
      </div>
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
<td>$<?= number_format($row['price'],2); ?></td>
<td>
<?php if($row['status']=='active'): ?>
<span class="badge badge-active">Active</span>
<?php else: ?>
<span class="badge badge-inactive">Inactive</span>
<?php endif; ?>
</td>
<td>
<div class="d-flex justify-content-center gap-2 flex-wrap">
    <button class="btn btn-sm btn-info viewBtn" 
        data-name="<?= htmlspecialchars($row['name']); ?>"
        data-category="<?= htmlspecialchars($row['category_name'] ?? 'No Category'); ?>"
        data-price="<?= number_format($row['price'],2); ?>"
        data-status="<?= $row['status']; ?>"
        data-description="<?= htmlspecialchars($row['description'] ?? ''); ?>"
        data-image="<?= htmlspecialchars($row['image'] ?? ''); ?>">
        <i class="bi bi-eye"></i> View
    </button>
    <a href="update_product.php?id=<?= $row['product_id']; ?>" class="btn btn-edit btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
    <a href="delete_product.php?id=<?= $row['product_id']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this product?')"><i class="bi bi-trash"></i> Delete</a>
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

<!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="modalName"></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<p><strong>Category:</strong> <span id="modalCategory"></span></p>
<p><strong>Price:</strong> $<span id="modalPrice"></span></p>
<p><strong>Status:</strong> <span id="modalStatus"></span></p>
<p><strong>Description:</strong> <span id="modalDescription"></span></p>
<div id="modalImageContainer" class="mt-2 text-center"></div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live Search
document.getElementById("searchBox").addEventListener("keyup", function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll("#productTable tbody tr").forEach(row => {
        const name = row.cells[1].innerText.toLowerCase();
        const category = row.cells[2].innerText.toLowerCase();
        row.style.display = (name.includes(query) || category.includes(query)) ? "" : "none";
    });
});

// View modal
const modal = new bootstrap.Modal(document.getElementById('productModal'));
document.querySelectorAll(".viewBtn").forEach(btn=>{
    btn.addEventListener("click", ()=>{
        document.getElementById('modalName').textContent = btn.dataset.name;
        document.getElementById('modalCategory').textContent = btn.dataset.category;
        document.getElementById('modalPrice').textContent = btn.dataset.price;
        document.getElementById('modalStatus').textContent = btn.dataset.status;
        document.getElementById('modalDescription').textContent = btn.dataset.description || 'N/A';

        const imgContainer = document.getElementById('modalImageContainer');
        imgContainer.innerHTML = '';
        if(btn.dataset.image && btn.dataset.image.trim() !== ''){
            const img = document.createElement('img');
            img.src = btn.dataset.image.startsWith('uploads/') ? btn.dataset.image : 'uploads/' + btn.dataset.image;
            img.className = 'img-fluid rounded';
            imgContainer.appendChild(img);
        }
        modal.show();
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>
 