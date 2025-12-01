<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

// Get logged-in user info
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
    $stmt = $conn->prepare("SELECT * FROM profile $search_sql ORDER BY employee_id_no+0 DESC");
    $stmt->bind_param("ssss", ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM profile ORDER BY employee_id_no+0 DESC");
}

// Get total rows for descending numbering
$total_rows = $result->num_rows;
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Profiles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }

        /* NAVBAR */
        .navbar-custom {
            background: linear-gradient(#6a11cb 0%, #2575fc 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            padding: 0.5rem 1rem;
            border-radius: 0 0 20px 20px;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .navbar-custom .navbar-brand { font-weight: 700; font-size: 1.4rem; color: #fff !important; }
        .navbar-custom .nav-link { color: rgba(255,255,255,0.9) !important; transition:0.3s; }
        .navbar-custom .nav-link:hover { color: #fff !important; }

        /* SEARCH BAR */
        .search-container { position: relative; }
        .search-container input { border-radius: 50px; padding-right: 40px; height: 35px; }
        .search-container button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; }

        /* TABLE CONTAINER */
        .table-container {
            max-width: 95%;
            margin: 30px auto;
            background: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
        .table-container h3 { color: #2575fc; font-weight: bold; }

        /* TABLE */
        table { width: 100%; table-layout: fixed; }
        table th, table td {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        table th { background: #2575fc; color: #fff; }
        table tbody tr:hover { background: rgba(37,117,252,0.1); transition: 0.3s; }
        .profile-img { width: 60px; height: 60px; object-fit: cover; border-radius: 12px; border: 2px solid #2575fc; }

        .btn-edit {
            background: linear-gradient(90deg,#6a11cb,#2575fc);
            color: #fff;
            border-radius: 50px;
            padding: 5px 15px;
            font-weight: 500;
            transition: 0.3s;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        .btn-edit:hover { background: linear-gradient(90deg,#2575fc,#1a4cb2); }

        .btn-add {
            background: linear-gradient(90deg,#28a745,#20c997);
            color: #fff;
            font-weight: 500;
            border-radius: 50px;
            padding: 6px 18px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        .btn-add:hover { background: linear-gradient(90deg,#20c997,#198754); }

        /* TOOLTIP */
        td[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            white-space: nowrap;
            z-index: 1000;
        }

        @media(max-width:1200px){ .profile-img{width:50px;height:50px;} }
        @media(max-width:768px){ table th, table td{ font-size:12px; padding:5px; } }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom shadow-sm mb-4">
<div class="container">
    <a class="navbar-brand" href="../home/index.php"><i class="bi bi-people-fill me-2"></i>Employee Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
    </button>

<div class="collapse navbar-collapse justify-content-end align-items-center" id="navbarNav">
    <form class="d-flex me-3" method="get">
        <div class="search-container">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search Employee..." value="<?= htmlspecialchars($search) ?>">
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

<div class="table-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>All Profiles</h3>
        <a href="add_profile.php" class="btn btn-add"><i class="fas fa-plus-circle me-2"></i>Add New Profile</a>
    </div>

    <table class="table table-hover table-bordered align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Name English</th>
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if($result->num_rows > 0):
            $counter = $total_rows; // descending numbering based on Employee ID
            while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $counter-- ?></td>
                <td><img src="../uploads/<?= $row['photo'] ?: 'default.png' ?>" class="profile-img" alt="Photo"></td>
                <td data-tooltip="<?= htmlspecialchars($row['name_en']) ?>"><?= htmlspecialchars($row['name_en']) ?></td>
                <td data-tooltip="<?= htmlspecialchars($row['username']) ?>"><?= htmlspecialchars($row['username']) ?></td>
                <td data-tooltip="<?= htmlspecialchars($row['employee_id_no']) ?>"><?= htmlspecialchars($row['employee_id_no']) ?></td>
                <td data-tooltip="<?= htmlspecialchars($row['position']) ?>"><?= htmlspecialchars($row['position']) ?></td>
                <td><?= htmlspecialchars($row['gender']) ?></td>
                <td><?= $row['birth_date'] ? date('d-M-Y', strtotime($row['birth_date'])) : '-' ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td data-tooltip="<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['start_date'] ? date('d-M-Y', strtotime($row['start_date'])) : '-' ?></td>
                <td><?= $row['resign_date'] ? date('d-M-Y', strtotime($row['resign_date'])) : '-' ?></td>
                <td><?= htmlspecialchars($row['marital_status']) ?></td>
                <td><?= htmlspecialchars($row['place_of_birth']) ?></td>
                <td data-tooltip="<?= htmlspecialchars($row['current_address']) ?>"><?= htmlspecialchars($row['current_address']) ?></td>
                <td><a href="edit_profile.php?id=<?= $row['id'] ?>" class="btn btn-edit btn-sm"><i class="fas fa-edit"></i> Edit</a></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="16" class="text-center">No profiles found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
