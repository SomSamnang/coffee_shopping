<?php
session_start();
require_once('../connection/db_connect.php');

// Auto-clear search on F5
if (isset($_GET['search']) && isset($_SERVER['HTTP_CACHE_CONTROL']) 
    && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
    header("Location: employee_list.php");
    exit();
}

// Get logged-in user info
$user_id = $_SESSION['user_id'] ?? null;
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// Fetch current user info
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

// SEARCH
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM employee WHERE name LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Store employees
$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee List</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { font-family: 'Poppins', sans-serif; padding-top:80px; overflow-x:hidden; }
.navbar-custom { background: linear-gradient(90deg,#6a11cb,#2575fc); border-radius:0 0 20px 20px; padding:0.5rem 1rem; position:fixed; width:100%; top:0; z-index:1050; }
.navbar-custom .navbar-brand { color:#fff; font-weight:700; font-size:1.5rem; }
.staff-photo { width:50px; height:50px; object-fit:cover; border-radius:12px; border:2px solid #2575fc; }
.status-badge { padding:5px 12px; border-radius:50px; font-weight:600; font-size:0.75rem; color:#fff; }
.bg-active { background:#28a745; } 
.bg-inactive { background:#6c757d; }
.search-container { position:relative; max-width:350px; }
.search-container input { border-radius:50px; padding-right:40px; height:38px; }
.search-container button { position:absolute; right:10px; top:50%; transform:translateY(-50%); border:none; background:none; color:#555; }
@media(max-width:768px){ .table-container table th, .table-container table td{ font-size:0.65rem; } }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
<div class="container">
<a class="navbar-brand" href="../home/index.php"><i class="bi bi-people-fill me-2"></i>Employee Management</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon" style="filter: invert(1);"></span>
</button>
<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
<form class="d-flex me-3" method="get">
<div class="search-container">
<input type="text" name="search" class="form-control form-control-sm" placeholder="Search Employee..." value="<?= htmlspecialchars($search) ?>">
<button type="submit"><i class="bi bi-search"></i></button>
</div>
</form>

<ul class="navbar-nav">
<?php if($currentUser): ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser) ?></a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="../my_profile/my_profile.php"><i class="bi bi-person me-2 text-primary"></i>My Profile</a></li>
<li><a class="dropdown-item" href="../my_profile/my_profile_list.php"><i class="bi bi-card-list me-2 text-info"></i>Profile List</a></li>
<li><a class="dropdown-item" href="../positions/position_list.php"><i class="bi bi-briefcase me-2 text-warning"></i>Positions</a></li>
<?php if($role==='admin'): ?>
<li><a class="dropdown-item" href="../users/user_list.php"><i class="bi bi-people-fill me-2 text-danger"></i>Users</a></li>
<?php endif; ?>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item text-danger" href="../users/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
</ul>
</li>
<?php endif; ?>
</ul>
</div>
</div>
</nav>

<div class="container table-container my-5">

<div class="d-flex justify-content-between align-items-center mb-3">
<h2 class="fw-bold text-primary"><i class="bi bi-people-fill me-2 text-warning"></i>Employee Lists</h2>
<a href="../employees/add_employee.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Employee</a>
</div>

<!-- DESKTOP TABLE -->
<div class="table-responsive d-none d-md-block shadow-sm rounded">
<table class="table table-bordered table-hover bg-white text-center align-middle">
<thead class="table-primary">
<tr>
<th>No</th>
<th>Photo</th>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Position</th>
<th>Start Date</th>
<th>Resign Date</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if(count($employees) > 0): $num=count($employees); foreach($employees as $row): ?>
<tr>
<td><?= $num-- ?></td>
<td><img src="<?= $row['photo'] ? '../uploads/'.$row['photo'] : 'https://via.placeholder.com/60' ?>" class="staff-photo"></td>
<td><?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['phone']) ?></td>
<td><?= htmlspecialchars($row['position']) ?></td>
<td><?= $row['start_date'] ? date('d-M-Y', strtotime($row['start_date'])) : '-' ?></td>
<td style="color: <?= $row['resign_date'] ? 'red' : 'blue' ?>;"><?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : 'Working' ?></td>
<td><span class="status-badge <?= $row['status']=='active'?'bg-active':'bg-inactive' ?>"><?= ucfirst($row['status']) ?></span></td>
<td>
<a href="../employees/employee_card.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" target="_blank"><i class="bi bi-printer"></i></a>
<a href="../employees/edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
<a href="../employees/delete_employee.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="11">No employees found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- MOBILE CARDS -->
<div class="d-block d-md-none">
<?php if(count($employees) > 0): $num=count($employees); foreach($employees as $row): ?>
<div class="card mb-3 shadow-sm">
<div class="row g-0 align-items-center">
<div class="col-4 text-center p-2">
<img src="<?= $row['photo'] ? '../uploads/'.$row['photo'] : 'https://via.placeholder.com/80' ?>" class="img-fluid rounded-circle">
</div>
<div class="col-8">
<div class="card-body p-2">
<h5 class="card-title mb-1"><?= $num-- ?>. <?= htmlspecialchars($row['name']) ?></h5>
<p class="mb-1"><strong>ID:</strong> <?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?></p>
<p class="mb-1"><strong>Position:</strong> <?= htmlspecialchars($row['position']) ?></p>
<p class="mb-1"><strong>Status:</strong> <span class="badge <?= $row['status']=='active'?'bg-success':'bg-secondary' ?>"><?= ucfirst($row['status']) ?></span></p>
<p class="mb-1"><strong>Resign Date:</strong> <?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : 'Working' ?></p>
<div class="d-flex justify-content-between mt-2">
<a href="../employees/employee_card.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" target="_blank"><i class="bi bi-printer"></i></a>
<a href="../employees/edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
<a href="../employees/delete_employee.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
</div>
</div>
</div>
</div>
</div>
<?php endforeach; else: ?>
<p class="text-center">No employees found.</p>
<?php endif; ?>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
