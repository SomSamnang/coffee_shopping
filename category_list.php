<?php
session_start();
require_once 'db_connect.php'; 

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
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f8fa;
    margin: 0;
    padding: 0;
}

/* Navbar */
.navbar {
    color: white;
    padding: 0.5rem 1rem;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.navbar-brand { color: white; font-weight: 600; }
.navbar-nav .nav-link { color: white; margin-right: 8px; font-weight: 500; }
.navbar-nav .nav-link:hover { background: rgba(255,255,255,0.2); border-radius: 8px; }

/* Dropdown center under button */
.dropdown-menu-center { left: 50% !important; transform: translateX(-50%) !important; }

/* Header */
header {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    color: white;
    padding: 2px 15px;
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
    text-decoration: none;
    color: #0d6efd;
    background: #ffffff;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 500;
}
.nav-buttons a:hover {
    background: #f1f1f1;
}

/* Search container in header */
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
.search-container i { color: #0d6efd; }

/* Container */
.container {
    max-width: 900px;
    margin: 20px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}
/* Add Category button */
.btn-add {
    background-color: #198754;
    color: white;
    margin-bottom: 10px;
    font-weight: 500;
    padding: 6px 18px;
    border-radius: 10px;

    align-items: center;
    gap: 6px;
    transition: all 0.2s ease-in-out;
    text-decoration: none;
}
.btn-add:hover {
    background-color: #157347;
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}


/* Table */
.table { border-radius: 12px; overflow: hidden; }
.table th, .table td { padding: 12px 15px; text-align: center; }
.table th { background-color: #f4f4f4; }
.table tr:hover { background-color: #f1f7ff; }

/* Status badge */
.badge-active { background-color:#0f5132; color:#fff; padding:3px 8px; border-radius:12px; font-size:0.85rem; }
.badge-inactive { background-color:#842029; color:#fff; padding:3px 8px; border-radius:12px; font-size:0.85rem; }

/* Action Buttons */
.btn-action { padding: 5px 12px; border-radius: 6px; font-size: 14px; margin-right: 5px; color: white; font-weight: 500; text-decoration: none; }
.edit-btn { background-color: #2196F3; }
.edit-btn:hover { background-color: #1976d2; }
.delete-btn { background-color: #f44336; }
.delete-btn:hover { background-color: #c62828; }

/* Responsive */
@media (max-width:768px) {
    header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .search-container { width: 100%; }
    .nav-buttons { width: 100%; display: flex; gap: 6px; flex-wrap: wrap; }
}
</style>
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
                        <a class="dropdown-item" href="profile.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-person me-2" style="color:blue;"></i> Profile
                        </a>
                    </li>
                    <!-- Home -->
                    <li>
                        <a class="dropdown-item" href="index.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-house-door me-2" style="color:pink;"></i> Home
                        </a>
                    </li>
                    <!-- Users (admin only) -->
                    <?php if($role === 'admin'): ?>
                    <li>
                        <a class="dropdown-item" href="user_list.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-people-fill me-2" style="color:green;"></i> Users
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
                <a class="nav-link btn btn-outline-light btn-sm" href="login.php">
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
