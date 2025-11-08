<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$userResult = $conn->query("SELECT id, username, role, status, created_at FROM users ORDER BY id DESC");

$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #eef2f7; font-family: 'Poppins', sans-serif; }
.card { border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.table-hover tbody tr:hover { background-color: #e6f2ff; }
.badge-admin { background-color: #ff6b6b; }
.badge-user { background-color: #4bcffa; }
.badge-active { background-color: #198754; }
.badge-inactive { background-color: #6c757d; }
#successOverlay { position: fixed; top:0; left:0; width:100%; height:100%; display:none; justify-content:center; align-items:center; background: rgba(0,0,0,0.4); z-index:1055;}
#successOverlay .overlay-content { background:white; padding:30px 50px; border-radius:12px; text-align:center; box-shadow:0 5px 20px rgba(0,0,0,0.2);}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg shadow-sm mb-4" style="background: linear-gradient(90deg, #4bcffa, #0d6efd);">
  <div class="container">
    <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="#">
      <i class="bi bi-people me-2"></i>User List
    </a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item me-3">
        <input class="form-control form-control-sm" type="search" placeholder="Search user..." id="userSearch" style="border-radius:20px;">
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($_SESSION['username']) ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li><a class="dropdown-item" href="index.php"><i class="bi bi-house-door me-2"></i>Home</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
</nav>

<div class="container">
<div class="card p-3 shadow-sm mb-3">
    <h4 class="text-primary mb-3"><i class="bi bi-people"></i> Users List</h4>
    <div class="table-responsive rounded">
        <table class="table table-hover table-bordered align-middle text-center mb-0" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($userResult && $userResult->num_rows > 0): ?>
                <?php while($row = $userResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td class="username"><?= htmlspecialchars($row['username']) ?></td>
                        <td><span class="badge rounded-pill text-white <?= $row['role']==='admin'?'badge-admin':'badge-user' ?>"><?= htmlspecialchars($row['role']) ?></span></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td><span class="badge rounded-pill text-white <?= $row['status']==='active'?'badge-active':'badge-inactive' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td>
                            <a href="user_management.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                            <a href="user_management.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="successOverlay">
  <div class="overlay-content">
    <div class="spinner-border text-success mb-3"></div>
    <div><?= htmlspecialchars($success_msg) ?></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if($success_msg): ?>
document.getElementById('successOverlay').style.display = 'flex';
setTimeout(()=>document.getElementById('successOverlay').style.display='none', 2000);
<?php endif; ?>

document.getElementById('userSearch').addEventListener('keyup', function(){
    const search=this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row=>{
        const username=row.querySelector('.username').textContent.toLowerCase();
        row.style.display=username.includes(search)?'':'none';
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
