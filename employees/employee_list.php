<?php
session_start();
require_once('../connection/db_connect.php');

/* ---------------------------------------------------------
    AUTO CLEAR SEARCH WHEN PAGE IS REFRESHED (F5)
----------------------------------------------------------*/
if (isset($_GET['search']) && isset($_SERVER['HTTP_CACHE_CONTROL']) 
    && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
    header("Location: employee_list.php");
    exit();
}

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

// SEARCH
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM employee WHERE name LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Store the result into an array
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
<title>Employee Lists</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="../css/employees_list.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
<div class="container">
    <a class="navbar-brand" href="../home/index.php">
        <i class="bi bi-people-fill me-2"></i>Employee Management
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">

        <!-- FIXED SEARCH FORM (NO ACTION="") -->
        <form class="d-flex me-3" method="get">
            <div class="search-container">
                <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search Employee..."
                        value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <ul class="navbar-nav">
            <?php if($currentUser): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown"> 
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser) ?> 
                </a> 
                <ul class="dropdown-menu dropdown-menu-end"> 
                    <li><a class="dropdown-item" href="my_profile.php"><i class="bi bi-person me-2 text-primary"></i>Profile</a></li> 
                    <li><a class="dropdown-item" href="../categories/category_list.php"><i class="bi bi-list-ul me-2 text-success"></i>Category</a></li>
                    <li><a class="dropdown-item" href="../employees/employee_card_list.php"><i class="bi bi-people-fill me-2 text-secondary"></i>Employees</a></li>
                    <li><a class="dropdown-item" href="../positions/position_list.php"><i class="bi bi-briefcase me-2 text-warning"></i>Positions</a></li> 
                    <?php if($role==='admin'): ?> 
                    <li><a class="dropdown-item" href="../users/user_list.php"><i class="bi bi-people-fill me-2 text-danger"></i>Users</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../users/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li> 
                </ul> 
            </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light btn-sm" href="../users/login.php">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
                </li>
            <?php endif; ?>
        </ul>

    </div>
</div>
</nav>

<div class="container table-container my-5">

<div class="d-flex justify-content-between align-items-center mb-10">
    <h2 class="fw-bold text-primary">
        <i class="bi bi-people-fill me-2 text-warning"></i> Employee Lists
    </h2>

    <a href="../employees/add_employee.php" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Add Employees
    </a>
</div>

<!-- DESKTOP TABLE -->
<div class="table-container d-none d-md-block">
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-hover bg-white text-center align-middle full-width-table">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Photo</th>
                    <th>Employee ID</th>
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
            <?php if (count($employees) > 0): ?>
                <?php $num = 1; foreach($employees as $row): ?>
                <tr>
                    <td><?= $num ?></td>
                    <td>
                        <img src="<?= $row['photo'] ? '../uploads/'.$row['photo'] : 'https://via.placeholder.com/60' ?>" 
                             class="staff-photo">
                    </td>
                    <td><?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= $row['start_date'] ? date('d-M-Y', strtotime($row['start_date'])) : '-' ?></td>
                    <td><?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : '-' ?></td>
                    <td>
                        <span class="badge <?= $row['status']=='active' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>

                    <!-- ACTION BUTTONS -->
                    <td class="action-buttons">
                        <a href="../employees/employee_card.php?id=<?= $row['id'] ?>" 
                           target="_blank" 
                           class="btn-action btn-print">
                            <i class="bi bi-printer"></i>
                        </a>

                        <a href="../employees/edit_employee.php?id=<?= $row['id'] ?>" 
                           class="btn-action btn-edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>

                        <a href="../employees/delete_employee.php?id=<?= $row['id'] ?>" 
                           onclick="return confirm('Are you sure?')" 
                           class="btn-action btn-delete">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>

                </tr>
                <?php $num++; endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11">No employees found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
