<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$current_user = $_SESSION['username'];
$username = $_GET['username'] ?? $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Profile not found!");
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($user['username']) ?> - Profile</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
   
    font-family: "Segoe UI", sans-serif;
    min-height: 100vh;
    padding-top: 80px;
}

/* Navbar */
.navbar-custom {
    background: linear-gradient(90deg, #6a11cb, #2575fc);
    padding: 8px 20px;
}
.navbar-custom .nav-link, .navbar-custom .navbar-brand {
    color: #fff !important;
    font-weight: 500;
    transition: color 0.3s;
}
.navbar-custom .nav-link:hover {
    color: #ffd700 !important;
}
.dropdown-menu .dropdown-item {
    transition: all 0.2s;
}
.dropdown-menu .dropdown-item:hover {
    background: rgba(38, 0, 255, 0.05);
    border-radius: 8px;
}

/* Profile Card */
.profile-card {
    background: #fff;
    border-radius: 20px;
    padding: 30px;
    margin-top: 30px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    transition: transform 0.3s ease;
}
.profile-card:hover {
    transform: translateY(-5px);
}

/* Profile Image */
.profile-img {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    border: 5px solid #6a11cb;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

/* Header */
.profile-header {
    text-align: center;
    margin-bottom: 20px;
}
.profile-header h2 {
    font-weight: 700;
    margin-top: 15px;
    color: #2575fc;
}
.kh-name {
    font-family: 'Khmer OS Battambang', sans-serif;
    font-size: 18px;
    color: #6a11cb;
    font-weight: 600;
}

/* Info Titles */
.info-title {
    font-weight: bold;
    margin-right: 5px;
}
.info-value {
    color: #6a11cb;
    font-weight: 600;
}
.profile-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.profile-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}
.badge {
    font-size: 0.85rem;
    padding: 0.35em 0.6em;
}
.navbar-custom {
    background: linear-gradient(90deg, #6a11cb, #2575fc);
    padding: 8px 20px;
    transition: background 0.3s;
}
.navbar-custom .nav-link {
    color: #fff !important;
    font-weight: 500;
    transition: color 0.3s, background 0.3s;
}
.navbar-custom .nav-link:hover {
    color: #ffd700 !important;
}
.navbar-custom .dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    padding: 5px 0;
}
.navbar-custom .dropdown-item {
    transition: all 0.2s;
    border-radius: 8px;
}
.navbar-custom .dropdown-item:hover {
    background: rgba(76, 0, 255, 0.1);
    color: #fff;
}
.navbar-brand i {
    color: #fff;
}

/* Colored Icons */
.icon-id { color: #ff4500; }
.icon-position { color: #1e90ff; }
.icon-gender { color: #ff69b4; }
.icon-birth { color: #32cd32; }
.icon-phone { color: #ffa500; }
.icon-email { color: #ff6347; }
.icon-marital { color: #8a2be2; }
.icon-birthplace { color: #20b2aa; }
.icon-address { color: #dc143c; }

/* Responsive */
@media(max-width:768px){
    .profile-card {
        padding: 20px;
    }
    .profile-img {
        width: 110px;
        height: 110px;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="../home/index.php">
      <i class="fa fa-user-circle me-2" style="font-size:1.3rem;"></i> Profile System
    </a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <!-- User Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
            <img src="../uploads/<?= $user['photo'] ?: 'default.png' ?>" class="rounded-circle me-2" style="width:38px; height:38px; object-fit:cover; border:2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
            <span style="color:#fff; font-weight:500;"><?= htmlspecialchars($current_user) ?></span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3" style="min-width:200px;">
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../my_profile/my_profile.php">
                <i class="fa fa-id-card text-primary me-2"></i> My Profile
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../orders/orders.php">
                <i class="fa fa-basket-shopping text-success me-2"></i> Orders
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../users/change_password.php">
                <i class="fa fa-key text-warning me-2"></i> Change Password
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center text-danger" href="../users/logout.php">
                <i class="fa fa-power-off me-2"></i> Logout
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>




<!-- Profile Card -->
<div class="container my-4">
  <div class="profile-card p-4" style="max-width:800px; margin:auto;">

    <!-- Header -->
    <div class="profile-header mb-3">
      <img src="../uploads/<?= $user['photo'] ?: 'default.png' ?>" class="profile-img" style="width:100px; height:100px; border:3px solid #6a11cb;">
      <h3 class="mt-2" style="color:#2575fc; font-weight:700;"><?= htmlspecialchars($user['name_en']) ?></h3>
      <div class="kh-name" style="font-size:16px; color:#6a11cb; font-weight:600;"><?= htmlspecialchars($user['name_kh']) ?></div>
      <p class="text-muted mb-0">@<?= htmlspecialchars($user['username']) ?></p>
    </div>

    <hr class="my-2">

    <!-- Info Grid -->
    <div class="row g-2">
      <div class="col-6">
        <p class="mb-1"><i class="fa fa-id-card icon-id me-2"></i><strong>Employee ID:</strong> <?= htmlspecialchars($user['employee_id_no']) ?></span></p>
        <p class="mb-1"><i class="fa fa-briefcase icon-position me-2"></i><strong>Position:</strong><?= htmlspecialchars($user['position']) ?></span></p>
        <p class="mb-1"><i class="fa fa-venus-mars icon-gender me-2"></i><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></span></p>
        <p class="mb-1"><i class="fa fa-cake-candles icon-birth me-2"></i><strong>Birth Date:</strong><?= $user['birth_date'] ? date('d-M-Y', strtotime($user['birth_date'])) : '-' ?></span></p>
      </div>
      <div class="col-6">
        <p class="mb-1"><i class="fa fa-phone icon-phone me-2"></i><strong>Phone:</strong><?= htmlspecialchars($user['phone']) ?></span></p>
        <p class="mb-1"><i class="fa fa-envelope icon-email me-2"></i><strong>Email:</strong><?= htmlspecialchars($user['email']) ?></span></p>
        <p class="mb-1"><i class="fa fa-ring icon-marital me-2"></i><strong>Marital:</strong><?= htmlspecialchars($user['marital_status']) ?></span></p>
        <p class="mb-1"><i class="fa fa-location-dot icon-birthplace me-2"></i><strong>Birthplace:</strong> <?= htmlspecialchars($user['place_of_birth']) ?></span></p>
      </div>
    </div>

    <hr class="my-2">

    <p class="mb-0"><i class="fa fa-map-pin icon-address me-2"></i><strong>Address:</strong> <?= htmlspecialchars($user['current_address']) ?></span></p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
