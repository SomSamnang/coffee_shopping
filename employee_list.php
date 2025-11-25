<?php
session_start();
require_once 'db_connect.php';

// Get logged-in user info
$user_id = $_SESSION['user_id'] ?? null;
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

$user_name = '';
$user_photo = '';

if ($user_id) {
    $stmt = $conn->prepare("SELECT name, photo FROM employee WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_name, $user_photo);
    $stmt->fetch();
    $stmt->close();
}

// Handle search
$search = $_GET['search'] ?? '';
$search_sql = '';
if ($search) {
    $search_sql = "WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                   OR email LIKE '%" . $conn->real_escape_string($search) . "%' 
                   OR phone LIKE '%" . $conn->real_escape_string($search) . "%' 
                   OR position LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Fetch employee
$sql = "SELECT * FROM employee $search_sql ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Lists</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { 
    background: #f4f6f9; 
    font-family: 'Inter', sans-serif; 
}
.navbar-custom {
    background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    padding: 0.5rem 1rem;
    border-radius: 0 0 20px 20px;
      /* Sticky */
    position: sticky;
    top: 0;
    z-index: 1020;
}
.navbar-custom .navbar-brand {
    font-weight: 700;
    font-size: 1.4rem;
    color: #fff !important;
}
.navbar-custom .nav-link {
    color: rgba(255,255,255,0.9) !important;
    transition: 0.3s;
}
.navbar-custom .nav-link:hover {
    color: #fff !important;
}
.navbar-custom .btn-outline-light {
    border-radius: 20px;
    transition: 0.3s;
}
.navbar-custom .dropdown-menu {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.table-hover tbody tr:hover { background-color: #f1f1f1; }
.staff-photo { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid #dee2e6; }
.card-staff { border-radius: 15px; transition: 0.3s; }
.card-staff:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.search-container { position: relative; }
.search-container input { border-radius: 50px; padding-right: 40px; padding-left: 15px; height: 35px; }
.search-container button { position: absolute; right: 5px; top: 50%; transform: translateY(-50%); border:none; background:none; color: gray; cursor: pointer; }

@media (max-width: 768px) { 
    .table-responsive { display: none; } 
    .staff-cards { display: block; } 
}
@media (min-width: 769px) { 
    .staff-cards { display: none; } 
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
<div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-people-fill me-2"></i>Employee Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">
        <!-- Search -->
        <form class="d-flex me-3" method="get" action="">
            <div class="search-container">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Employee..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <!-- User Dropdown -->
        <ul class="navbar-nav">
        <?php if($currentUser): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($currentUser) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="my_profile.php"><i class="bi bi-person me-2 text-primary"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="category_list.php"><i class="bi bi-list-ul me-2 text-success"></i>Category</a></li>
                    <li><a class="dropdown-item" href="position_list.php"><i class="bi bi-briefcase me-2 text-warning"></i>Positions</a></li>
                    <?php if($role==='admin'): ?>
                        <li><a class="dropdown-item" href="user_list.php"><i class="bi bi-people-fill me-2 text-danger"></i>Users</a></li>
                        <li><a class="dropdown-item" href="index.php"><i class="bi bi-house-door me-2 text-info"></i>Home</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link btn btn-outline-light btn-sm" href="login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
            </li>
        <?php endif; ?>
        </ul>
    </div>
</div>
</nav>

<div class="container my-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary"><i class="bi bi-people-fill me-2 text-warning"></i> Employee Lists</h2>
    <a href="add_employee.php" class="btn btn-success"><i class="bi bi-plus-lg me-1 text-white"></i> Add Employees</a>
</div>

<!-- TABLE VIEW -->
<div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-hover align-middle bg-white text-center">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Photo</th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Position</th>
                <th>Start</th>
                <th>Resign</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $row_number = 1; 
        while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row_number ?></td>
                <td><img src="<?= $row['photo'] ? 'uploads/'.$row['photo'] : 'https://via.placeholder.com/60' ?>" class="staff-photo"></td>
                <td><?= '' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['position']) ?></td>
                <td><?= $row['start_date'] ? date('d-M-Y', strtotime($row['start_date'])) : '-' ?></td>
                <td style="color: <?= $row['resign_date'] ? '#dc3545' : '#000' ?>; font-weight: <?= $row['resign_date'] ? '600' : '400' ?>;">
                    <?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : '-' ?>
                </td>
                <td>
                    <?php if($row['status']=='active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                    <a href="delete_employee.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php 
        $row_number++; 
        endwhile; ?>
        <?php if($result->num_rows == 0): ?>
            <tr><td colspan="11" class="text-center text-muted">No staff found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MOBILE CARD VIEW -->
<div class="staff-cards row g-3 mt-3">
    <?php 
    $result->data_seek(0);
    while($row = $result->fetch_assoc()): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card card-staff shadow-sm p-3">
            <div class="d-flex align-items-center">
                <img src="<?= $row['photo'] ? 'uploads/'.$row['photo'] : 'https://via.placeholder.com/60' ?>" class="staff-photo me-3">
                <div class="flex-grow-1">
                    <h5 class="mb-1"><?= htmlspecialchars($row['name']) ?></h5>
                    <p class="mb-0"><i class="bi bi-envelope"></i> <?= htmlspecialchars($row['email']) ?></p>
                    <p class="mb-0"><i class="bi bi-telephone"></i> <?= htmlspecialchars($row['phone']) ?></p>
                    <p class="mb-0"><strong>Position:</strong> <?= htmlspecialchars($row['position']) ?></p>
                    <p class="mb-0"><strong>Start:</strong> <?= $row['start_date'] ? date('d-M-Y', strtotime($row['start_date'])) : '-' ?></p>
                    <p class="mb-0"><strong>Resign:</strong> 
                        <span style="color: <?= $row['resign_date'] ? '#dc3545' : '#000' ?>; font-weight: <?= $row['resign_date'] ? '600' : '400' ?>;">
                            <?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : '-' ?>
                        </span>
                    </p>
                    <p class="mt-1">
                        <?php if($row['status']=='active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </p>
                    <div class="mt-2">
                        <a href="edit_employee.php" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil"></i> Edit</a>
                        <a href="delete_employee.php" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
