<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

$stmt = $conn->prepare("SELECT * FROM profile WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Profile not found");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        /* NAVBAR */
        .navbar-custom {
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            padding: 0.5rem 1rem;
            border-radius: 0 0 20px 20px;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: #fff !important;
        }
        .navbar-custom .nav-link { color: rgba(255,255,255,0.9) !important; transition:0.3s; }
        .navbar-custom .nav-link:hover { color: #fff !important; }
        .navbar-custom .dropdown-menu { min-width: 180px; }

        /* PROFILE CARD */
        .profile-card {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .profile-photo {
            width: 180px;
            height: 180px;
            border-radius: 15px;
            object-fit: cover;
            border: 3px solid #2575fc;
            margin-bottom: 15px;
        }
        .profile-name { font-size: 24px; font-weight: bold; color: #2575fc; }
        .profile-sub { font-size: 16px; color: #555; margin-bottom: 15px; }
        .info-box .label { font-weight: bold; color: #333; }
        @media(max-width: 768px) { .profile-photo { width: 120px; height: 120px; } }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><i class="bi bi-people-fill me-2"></i>Employee System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if($currentUser): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($currentUser) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="my_profile.php"><i class="bi bi-person me-2 text-primary"></i>Profile</a></li>
                        <?php if($role==='admin'): ?>
                        <li><a class="dropdown-item" href="../users/user_list.php"><i class="bi bi-people-fill me-2 text-danger"></i>Users</a></li>
                       <!-- home -->
                        <li>
                            
                            
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link btn btn-outline-light btn-sm" href="login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- PROFILE CARD -->
<div class="profile-card">
    <div class="row g-4">
        <div class="col-md-4 text-center">
            <img src="../uploads/<?= !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'default.png' ?>" class="profile-photo" alt="Profile">
            <div class="profile-name"><?= htmlspecialchars($user['name_en']) ?></div>
            <div class="profile-sub"><?= htmlspecialchars($user['name_kh']) ?></div>
        </div>
        <div class="col-md-8">
            <div class="info-box">
                <p><span class="label">Position:</span> <?= htmlspecialchars($user['position']) ?> (<?= htmlspecialchars($user['position_kh']) ?>)</p>
                <p><span class="label">Employee ID:</span> <?= htmlspecialchars($user['employee_id_no']) ?></p>
                <p><span class="label">Gender:</span> <?= htmlspecialchars($user['gender']) ?></p>
                <p><span class="label">Birth Date:</span> <?= $user['birth_date'] ? date('d-M-Y', strtotime($user['birth_date'])) : '-' ?></p>
                <p><span class="label">Phone:</span> <?= htmlspecialchars($user['phone']) ?></p>
                <p><span class="label">Email:</span> <?= htmlspecialchars($user['email']) ?></p>
                <p><span class="label">Start Date:</span> <?= $user['start_date'] ? date('d-M-Y', strtotime($user['start_date'])) : '-' ?></p>
                <p><span class="label">Resign Date:</span> <?= $user['resign_date'] ? date('d-M-Y', strtotime($user['resign_date'])) : '-' ?></p>
                <p><span class="label">Marital Status:</span> <?= htmlspecialchars($user['marital_status']) ?></p>
                <p><span class="label">Place of Birth:</span> <?= htmlspecialchars($user['place_of_birth']) ?></p>
                <p><span class="label">Address:</span> <?= htmlspecialchars($user['current_address']) ?></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
