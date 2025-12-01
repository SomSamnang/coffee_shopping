<?php
session_start();
require_once('../connection/db_connect.php');


if (isset($_GET['search']) && isset($_SERVER['HTTP_CACHE_CONTROL']) 
    && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {

    header("Location: ../positions/position_list.php");
    exit();
}


$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;


$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM positions WHERE position_name LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Position List</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="../css/position_list.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
<div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-people-fill me-2"></i>Position Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">

        <!-- Search -->
        <form class="d-flex me-3" method="get" action="">
            <div class="search-container">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Position..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <!-- User dropdown -->
        <ul class="navbar-nav">
        <?php if($currentUser): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../my_profile/my_profile.php"><i class="bi bi-person me-2 text-primary"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="../categories/category_list.php"><i class="bi bi-list-ul me-2 text-success"></i>Category</a></li>
                    <li><a class="dropdown-item" href="../employees/employee_list.php"><i class="bi bi-people me-2 text-warning"></i>Employee List</a></li>

                    <?php if($role === 'admin'): ?>
                        <li><a class="dropdown-item" href="../users/user_list.php"><i class="bi bi-people-fill me-2 text-danger"></i>Users</a></li>
                        <li><a class="dropdown-item" href="../home/index.php"><i class="bi bi-house-door me-2 text-info"></i>Home</a></li>
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

<!-- PAGE CONTENT -->
<div class="container">
    <div class="card p-4 mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="bi bi-list-task me-2"></i>Position List</h2>
            <a href="../positions/add_position.php" class="btn btn-add btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Position</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Position</th>
                    <th>Created Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['position_name']) ?></td>
                            <td>
                                <?= $row['created_at'] ? date('d-M-Y H:i A', strtotime($row['created_at'])) : '-' ?>
                            </td>


                            <td>
                                <?php if ($row['status'] == 1): ?>
                                    <span class="badge badge-active"><i class="bi bi-check-circle me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="../positions/edit_position.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm btn-action">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="../positions/delete_position.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm btn-action"
                                   onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            <i class="bi bi-exclamation-circle me-1"></i>No positions found
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>

            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
