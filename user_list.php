<?php
session_start();
require_once 'db_connect.php';

// Only admin can view
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle POST actions: add/update user or reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update user
    if (isset($_POST['user_modal'])) {
        $id = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if ($username && $role && $status) {
            if ($id > 0) {
                // Update user
                if ($password) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=?, status=? WHERE id=?");
                    $stmt->bind_param("ssssi", $username, $hashed, $role, $status, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username=?, role=?, status=? WHERE id=?");
                    $stmt->bind_param("sssi", $username, $role, $status, $id);
                }
                $stmt->execute();
                $stmt->close();
                $_SESSION['success_msg'] = "User updated successfully.";
            } else {
                // Add new user
                $check = $conn->prepare("SELECT id FROM users WHERE username=?");
                $check->bind_param("s", $username);
                $check->execute();
                $check->store_result();
                if ($check->num_rows > 0) {
                    $_SESSION['success_msg'] = "Username already exists.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $hashed, $role, $status);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['success_msg'] = "User added successfully.";
                }
                $check->close();
            }
        } else {
            $_SESSION['success_msg'] = "Please fill all required fields.";
        }
        header("Location: user_list.php");
        exit;
    }

    // Reset password
    if (isset($_POST['reset_password_modal'])) {
        $user_id = intval($_POST['reset_user_id']);
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass && $new_pass === $confirm_pass) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_msg'] = "Password reset successfully.";
        } else {
            $_SESSION['success_msg'] = "Passwords do not match or empty.";
        }
        header("Location: user_list.php");
        exit;
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_msg'] = "User deleted successfully.";
    header("Location: user_list.php");
    exit;
}

// Fetch all users
$userResult = $conn->query("SELECT id, username, password, role, status, created_at FROM users ORDER BY id DESC");

// Capture success message
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #eef2f7; font-family: 'Poppins', sans-serif; }
.card { border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.table-hover tbody tr:hover { background-color: #e6f2ff; }
.password-text { font-family: monospace; display: flex; align-items: center; justify-content: center; }
.eye-icon { cursor: pointer; color: #0d6efd; margin-left: 8px; }
.badge-admin { background-color: #ff6b6b; }
.badge-user { background-color: #4bcffa; }
.badge-active { background-color: #198754; }
.badge-inactive { background-color: #6c757d; }
#successOverlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4); display: none;
    justify-content: center; align-items: center; z-index: 1055;
}
#successOverlay .overlay-content {
    background: white; padding: 30px 50px;
    border-radius: 12px; text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm mb-4" style="background: linear-gradient(90deg, #4bcffa, #0d6efd);">
  <div class="container">
    <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="#">
      <i class="bi bi-person-gear me-2"></i> User Management
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-3">
          <input class="form-control form-control-sm" type="search" placeholder="Search user..." id="userSearch" style="border-radius:20px;">
        </li>
        <?php if($_SESSION['username']): ?>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="color:white;">
                <i class="bi bi-person-circle me-1" style="color:yellow;"></i>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2" style="color:blue;"></i> Profile</a></li>
                <?php if($_SESSION['role']==='admin'): ?>
                     <!-- Home -->
                    <li>
                        <a class="dropdown-item" href="index.php" style="color:blue; font-weight:500;">
                            <i class="bi bi-house-door me-2" style="color:pink;"></i> Home
                        </a>
                    </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2" style="color:red;"></i> Logout</a></li>
            </ul>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
<div class="card p-3 shadow-sm mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-primary"><i class="bi bi-people"></i> Users List</h4>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn"><i class="bi bi-plus-circle me-1"></i> Add User</button>
    </div>
    <div class="table-responsive rounded">
        <table class="table table-hover table-bordered align-middle text-center mb-0" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Password</th>
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
                        <td class="password-text" data-password="<?= htmlspecialchars($row['password']) ?>">•••••••••• <i class="bi bi-eye eye-icon"></i></td>
                        <td><span class="badge rounded-pill text-white <?= $row['role']==='admin'?'badge-admin':'badge-user' ?>"><?= htmlspecialchars($row['role']) ?></span></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td class="text-nowrap"><span class="badge rounded-pill text-white <?= $row['status']==='active'?'badge-active':'badge-inactive' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-warning resetBtn" data-id="<?= $row['id'] ?>" data-username="<?= htmlspecialchars($row['username']) ?>"><i class="bi bi-arrow-clockwise"></i></button>
                            <button class="btn btn-sm btn-primary editBtn" data-id="<?= $row['id'] ?>" data-username="<?= htmlspecialchars($row['username']) ?>" data-role="<?= $row['role'] ?>" data-status="<?= $row['status'] ?>" data-bs-toggle="modal" data-bs-target="#userModal"><i class="bi bi-pencil-square"></i></button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="userForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="userId">
        <input type="hidden" name="user_modal" value="1">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
          <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" id="role" class="form-select" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" id="status" class="form-select" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success" id="submitBtn">Add</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Password Reset Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="reset_user_id" id="reset_user_id">
        <input type="hidden" name="reset_password_modal" value="1">
        <p class="fw-semibold text-primary" id="reset_username"></p>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning">Confirm Reset</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Success Overlay -->
<div id="successOverlay">
  <div class="overlay-content">
    <div class="spinner-border text-success mb-3"></div>
    <div><?= htmlspecialchars($success_msg) ?></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show success overlay
<?php if($success_msg): ?>
document.getElementById('successOverlay').style.display = 'flex';
setTimeout(()=>document.getElementById('successOverlay').style.display='none', 2000);
<?php endif; ?>

// Edit user
document.querySelectorAll('.editBtn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        document.getElementById('modalTitle').textContent='Edit User';
        document.getElementById('userId').value=btn.dataset.id;
        document.getElementById('username').value=btn.dataset.username;
        document.getElementById('role').value=btn.dataset.role;
        document.getElementById('status').value=btn.dataset.status;
        document.getElementById('password').value='';
        document.getElementById('submitBtn').textContent='Update';
    });
});

// Add user modal
document.getElementById('addUserBtn').addEventListener('click', ()=>{
    document.getElementById('modalTitle').textContent='Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value='';
    document.getElementById('submitBtn').textContent='Add';
});

// Password toggle
document.querySelectorAll('.password-text .eye-icon').forEach(icon=>{
    icon.addEventListener('click', ()=>{
        const td = icon.parentElement;
        const password = td.getAttribute('data-password');
        if(td.firstChild.textContent==='••••••••••'){
            td.firstChild.textContent=password;
            icon.classList.replace('bi-eye','bi-eye-slash');
        } else {
            td.firstChild.textContent='••••••••••';
            icon.classList.replace('bi-eye-slash','bi-eye');
        }
    });
});

// Reset password modal
document.querySelectorAll('.resetBtn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        document.getElementById('reset_user_id').value=btn.dataset.id;
        document.getElementById('reset_username').textContent="Reset password for: "+btn.dataset.username;
        new bootstrap.Modal(document.getElementById('resetModal')).show();
    });
});

// Live search
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
