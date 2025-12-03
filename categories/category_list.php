<?php
session_start();
require_once('../connection/db_connect.php');

// Fetch logged-in user info
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// Fetch all categories
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Category Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/category_list.css">
</head>
<body>



<!-- Page Header -->
<header>
    <h1>Category List</h1>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="search-container">
            <input type="text" id="searchBox" placeholder="Search categories...">
            <i class="bi bi-search"></i>
        </div>

        <!-- Navbar with User Dropdown -->
<nav class="navbar navbar-expand-lg">
    <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav align-items-center">
            <?php if($currentUser): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1" style="color:yellow;"></i> <?= htmlspecialchars($currentUser) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-center">
                    <!-- Profile -->
                    <li>
                        <a class="dropdown-item" href="../my_profile/my_profile.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-person me-2" style="color:blue;"></i> Profile
                        </a>
                    </li>
                    <!-- Products -->
                    <li>
                        <a class="dropdown-item" href="../products/product.php" style="font-weight:500; color:blue;">
                            <i class="bi bi-cup me-2" style="color:brown;"></i> Products
                        </a>
                    </li>

                    <!-- Home -->
                    <li>
                        <a class="dropdown-item" href="../home/index.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-house-door me-2" style="color:pink;"></i> Home
                        </a>
                    </li>
                    <!-- Users (admin only) -->
                    <?php if($role === 'admin'): ?>
                    <li>
                        <a class="dropdown-item" href="../users/user_list.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-people-fill me-2" style="color:green;"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <!-- Logout -->
                    <li>
                        <a class="dropdown-item text-danger" href="../users/logout.php" style="color:blue;font-weight:500;">
                           <i class="fa fa-power-off me-2"style="color:red;"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm" href="../users/login.php">
                    <i class="bi bi-box-arrow-in-right me-2" style="color:green;"></i> Login
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
    </div>
</header>

<!-- Category Table -->
<div class="container">
    <!-- Add Category Button -->
    <div class="d-flex justify-content-end">
        <a href="add_category.php" class="btn-add">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>

        
    </div>
   <div class="table-responsive mt-2">
        <table class="table table-striped" id="categoryTable">
            
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="<?= $row['status'] === 'inactive' ? 'status-inactive' : 'status-active' ?>">
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['created_at'] ?? '-' ?></td>
                        <td>
                            <span class="<?= $row['status'] === 'inactive' ? 'badge-inactive' : 'badge-active' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a class="btn-action edit-btn" href="edit_category.php?id=<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a class="btn-action delete-btn" href="delete_category.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this category?');"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Categories not found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live search
document.getElementById('searchBox').addEventListener('keyup', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#categoryTable tbody tr').forEach(row => {
        row.style.display = row.cells[1].innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>
