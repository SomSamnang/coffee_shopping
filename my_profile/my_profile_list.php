<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// Handle search
$search = $_GET['search'] ?? '';
$search_sql = '';
$params = [];

if ($search) {
    $search_sql = "WHERE name_en LIKE ? OR employee_id_no LIKE ? OR position LIKE ? OR username LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}

// Fetch profiles sorted by employee_id_no numeric descending
if ($search_sql) {
    $stmt = $conn->prepare("
        SELECT * FROM profile 
        $search_sql 
        ORDER BY CAST(REPLACE(employee_id_no, 'ST-', '') AS UNSIGNED) DESC
    ");
    $stmt->bind_param("ssss", ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT * FROM profile
        ORDER BY CAST(REPLACE(employee_id_no, 'ST-', '') AS UNSIGNED) DESC
    ");
}

$total_rows = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>All Profiles</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
    font-family: "Poppins", sans-serif;

    min-height: 100vh;
    padding-top: 80px;
}
/* Remove scrollbars (disable scrolling) */
body {
  overflow-x: hidden; /* Hide horizontal scrollbar */
  overflow-y: auto; /* Allow vertical scroll but hide bar */
}

/* For WebKit browsers (Chrome, Edge, Safari) */
body::-webkit-scrollbar {
  width: 0px;
  background: transparent;
}
/* NAVBAR */
.navbar-custom {
    background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border-radius: 0 0 20px 20px;
    padding: 0.5rem 1rem;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1050;
}
.navbar-custom .navbar-brand {
    font-weight: 700;
    font-size: 1.6rem;
    color: #fff !important;
}

/* TABLE CARD */
.table-card {
    max-width: 98%;
    margin: 20px auto 50px;
    background: rgba(255,255,255,0.97);
    padding: 20px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}
.table-card h3 {
    font-weight: 700;
    color: #2575fc;
}

/* BUTTONS */
.btn-custom {
    border-radius: 50px;
    font-weight: 600;
    padding: 6px 15px;
    transition: 0.3s;
}
.btn-edit {
    background: linear-gradient(135deg, #0d6efd, #0056b3);
    color: #fff;
}
.btn-edit:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
}
.btn-delete {
    background: linear-gradient(135deg, #dc3545, #a71d2a);
    color: #fff;
}
.btn-delete:hover {
    background: linear-gradient(135deg, #a71d2a, #7f131b);
}
.btn-add {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: #fff;
}
.btn-add:hover {
    background: linear-gradient(135deg, #1e7e34, #155724);
}

/* SEARCH */
.search-container { position: relative; width: 100%; max-width: 350px; }
.search-container input { border-radius: 50px; padding-right: 40px; height: 38px; }
.search-container button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; color:#555; }

/* TABLE */
.table-responsive-wrapper {
    overflow-x: auto;
    width: 100%;
}
table { 
    width: 100%; 
    table-layout: auto; 
    border-radius: 12px; 
}
table th, table td { 
    text-align: center; 
    vertical-align: middle; 
    white-space: nowrap;
    overflow: hidden; 
    text-overflow: ellipsis; 
    font-size: 18px;
}
table th { 

    color: #fff; 
    font-weight: 600;
}
table tbody tr { 
    transition: 0.3s; 
    cursor: pointer;
}
table tbody tr:hover { 
    background: rgba(37,117,252,0.1); 
    transform: translateY(-2px); 
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* PROFILE IMAGE */
.profile-img { 
    width: 50px; 
    height: 50px; 
    object-fit: cover; 
    border-radius: 12px; 
    border: 2px solid #2575fc; 
}

/* STATUS BADGES */
.status-badge { padding: 5px 12px; border-radius: 50px; font-weight: 600; color: #fff; font-size: 0.7rem; } 
.status-active { background: #28a745; } 
.status-inactive { background: #6c757d; }

/* RESPONSIVE */
@media(max-width:1200px){
    table th, table td { font-size:0.7rem; }
}
@media(max-width:768px){
    .table-card { padding: 15px; }
    table th, table td { font-size:0.65rem; }
    .search-container { max-width: 100%; margin-bottom:10px; }
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand" href="../home/index.php"><i class="bi bi-people-fill me-2"></i>Profile Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">
      <form class="d-flex me-3" method="get">
        <div class="search-container">
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Profile..." value="<?= htmlspecialchars($search) ?>">
          <button type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
      <ul class="navbar-nav">
        <?php if($currentUser): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="../my_profile/my_profile.php"><i class="bi bi-person me-2 text-primary"></i>Profile</a></li>
            <li><a class="dropdown-item" href="../categories/category_list.php"><i class="bi bi-list-ul me-2 text-success"></i>Category</a></li>
            <li><a class="dropdown-item" href="../employees/employee_list.php"><i class="bi bi-people-fill me-2 text-warning"></i>Employees</a></li>
            <li><a class="dropdown-item" href="../positions/position_list.php"><i class="bi bi-briefcase me-2 text-warning"></i>Positions</a></li>
            <?php if($role==='admin'): ?>
            <li><a class="dropdown-item" href="../users/user_list.php"><i class="bi bi-people-fill me-2 text-danger"></i>Users</a></li>
            <li><a class="dropdown-item" href="../my_profile/my_profile_list.php"><i class="bi bi-card-list me-2 text-info"></i>Profile List</a></li>
            <li><a class="dropdown-item" href="../home/index.php"><i class="bi bi-house-door-fill me-2 text-success"></i>Home</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../users/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="nav-link btn btn-outline-light btn-sm" href="../users/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Add Bootstrap JS at the bottom of body -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<div class="table-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h3>All Profiles</h3>
        <div class="d-flex flex-wrap gap-2">
            
            <a href="add_profile.php" class="btn btn-add btn-custom"><i class="fas fa-plus-circle me-2"></i>Add Profile</a>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Name EN</th>
                    <th>Username</th>
                    <th>Employee ID</th>
                    <th>Position</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Start Date</th>
                    <th>Resign Date</th>
                    <th>Marital Status</th>
                    <th>Place of Birth</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result->num_rows > 0):
                $counter = $total_rows;
                while($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $counter-- ?></td>
                    <td><img src="../uploads/<?= $row['photo'] ?: 'default.png' ?>" class="profile-img"></td>
                    <td><?= htmlspecialchars($row['name_en']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['employee_id_no']) ?></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= htmlspecialchars($row['gender']) ?></td>
                    <td><?= $row['birth_date'] ? date('d-M-Y', strtotime($row['birth_date'])) : '-' ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['start_date'] ? date('d-M-Y', strtotime($row['start_date'])) : '-' ?></td>
                    <td style="color: <?= $row['resign_date'] ? 'red' : 'blue' ?>;">
    <?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : 'Working' ?>
</td>

                    <td><?= htmlspecialchars($row['marital_status']) ?></td>
                    <td><?= htmlspecialchars($row['place_of_birth']) ?></td>
                    <td class="current-address" title="<?= htmlspecialchars($row['current_address']) ?>"><?= htmlspecialchars($row['current_address']) ?></td>
                    <td> <span class="status-badge <?= $row['status']=='active'?'status-active':'status-inactive' ?>"> <?= ucfirst($row['status']) ?> </span> </td>
                    <td>
                        <a href="edit_profile.php?id=<?= $row['id'] ?>" class="btn btn-edit btn-custom"><i class="fas fa-edit"></i> Edit</a>
                        <a href="delete_profile.php?id=<?= $row['id'] ?>" 
                           class="btn btn-delete btn-custom" 
                           onclick="return confirm('Are you sure you want to delete this profile?');">
                           <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="17" class="text-center">No profiles found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
