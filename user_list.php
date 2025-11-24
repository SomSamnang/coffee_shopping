<?php
session_start();
require_once 'db_connect.php';

// Only admin can view
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add/Edit User
    if (isset($_POST['user_modal'])) {
        $id = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if ($username && $email && $role && $status) {
            if ($id > 0) {
                // Update user
                if ($password) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, role=?, status=?, display_password=? WHERE id=?");
                    $stmt->bind_param("ssssssi", $username, $email, $hashed, $role, $status, $password, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?");
                    $stmt->bind_param("ssssi", $username, $email, $role, $status, $id);
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
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, display_password) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $username, $email, $hashed, $role, $status, $password);
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

    // Reset Password
    if (isset($_POST['reset_password_modal'])) {
        $user_id = intval($_POST['reset_user_id']);
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if ($new_pass && $new_pass === $confirm_pass) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=?, display_password=? WHERE id=?");
            $stmt->bind_param("ssi", $hashed, $new_pass, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_msg'] = "Password reset successfully.";
        } else {
            $_SESSION['success_msg'] = "Passwords do not match or empty.";
        }
        header("Location: user_list.php");
        exit;
    }

    // Delete user
    if (isset($_POST['delete_user_id'])) {
        $del_id = intval($_POST['delete_user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_msg'] = "User deleted successfully.";
        header("Location: user_list.php");
        exit;
    }
}

// Fetch all users
$userResult = $conn->query("SELECT id, username, email, role, status, created_at, display_password FROM users ORDER BY id DESC");
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
<link rel="stylesheet" href="style/user_list.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm mb-4" style="background: linear-gradient(90deg, #4bcffa, #0d6efd);">
<div class="container">
<a class="navbar-brand text-white fw-bold d-flex align-items-center" href="#"><i class="bi bi-person-gear me-2"></i> User Management</a>
<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
<span class="navbar-toggler-icon" style="filter: invert(1);"></span>
</button>
<div class="collapse navbar-collapse" id="navbarContent">
<ul class="navbar-nav ms-auto align-items-center">
<li class="nav-item me-3">
  <div style="position: relative; width: 200px;"> 
    <input type="text" id="userSearch" class="form-control form-control-sm" 
           placeholder="Search..." 
           style="border-radius: 10px; padding-right: 30px; height: 30px; font-size: 0.85rem;">
    <i class="bi bi-search" 
       style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); color: #6c757d; font-size: 0.9rem;"></i>
  </div>
</li>

<?php if($_SESSION['username']): ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="color:white;">
<i class="bi bi-person-circle me-1" style="color:yellow;"></i>
<?= htmlspecialchars($_SESSION['username']) ?>
</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2" style="color:blue;"></i> Profile</a></li>
<li><a class="dropdown-item" href="index.php" style="color:blue; font-weight:500;"><i class="bi bi-house-door me-2" style="color:pink;"></i> Home</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2" style="color:red;"></i> Logout</a></li>
</ul>
</li>
<?php endif; ?>
</ul>
</div>
</div>
</nav>

<div class="container" id="usersContainer">
<div class="card p-4 shadow-sm mb-3">
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="text-primary"><i class="bi bi-people"></i> Users List</h4>
<a href="register.php" id="addUserBtn" class="btn btn-primary shadow-sm px-3">
  <i class="bi bi-person-plus-fill me-1"></i> Create User
</a>
</div>

<div class="table-responsive rounded">
<table class="table table-hover table-bordered align-middle text-center mb-0" id="usersTable">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Username</th>
<th>Email</th>
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
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td class="password-text" data-password="<?= htmlspecialchars($row['display_password']) ?>">
        <span class="password-dots">******</span>
        <i class="bi bi-eye eye-icon"></i>
    </td>
    <td>
        <span class="badge rounded-pill text-white <?= $row['role']==='admin'?'badge-admin':'badge-user' ?>">
            <?= htmlspecialchars($row['role']) ?>
        </span>
    </td>
    <td><?= date("Y-m-d | h:i A", strtotime($row['created_at'])) ?></td>
    <td>
        <span class="badge rounded-pill text-white <?= $row['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= htmlspecialchars($row['status']) ?>
        </span>
    </td>
    <td>
        <button class="btn btn-sm btn-warning resetBtn" data-id="<?= $row['id'] ?>" data-username="<?= htmlspecialchars($row['username']) ?>"><i class="bi bi-arrow-clockwise text-white"></i></button>
        <button class="btn btn-sm btn-primary editBtn" 
                data-id="<?= $row['id'] ?>" 
                data-username="<?= htmlspecialchars($row['username']) ?>" 
                data-email="<?= htmlspecialchars($row['email']) ?>" 
                data-password="<?= htmlspecialchars($row['display_password']) ?>" 
                data-role="<?= $row['role'] ?>" 
                data-status="<?= $row['status'] ?>">
            <i class="bi bi-pencil-square text-white"></i>
        </button>
        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
            <input type="hidden" name="delete_user_id" value="<?= $row['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash text-white"></i></button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8">No users found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
<div class="modal-dialog">
<form method="POST" class="modal-content p-4" id="userForm">
<h4 class="mb-3 text-primary" id="modalTitle">Add User</h4>
<input type="hidden" name="id" id="userId">
<input type="hidden" name="user_modal" value="1">
<div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="email" class="form-control" placeholder="Enter email" required></div>
<div class="mb-3"><label class="form-label">Password</label><input type="text" name="password" id="password" class="form-control" placeholder="Enter password"></div>
<div class="mb-3"><label class="form-label">Role</label><select name="role" id="role" class="form-select" required><option value="">Select Role</option><option value="admin">Admin</option><option value="user">User</option></select></div>
<div class="mb-3"><label class="form-label">Status</label><select name="status" id="status" class="form-select" required><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
<div class="d-flex justify-content-end">
<button type="submit" class="btn btn-success me-2"><i class="bi bi-check-circle me-1"></i> Save</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>
</form>
</div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
<div class="modal-dialog">
<form method="POST" class="modal-content p-4" id="resetForm">
<h4 class="mb-3 text-warning">Reset Password</h4>
<input type="hidden" name="reset_user_id" id="reset_user_id">
<input type="hidden" name="reset_password_modal" value="1">
<p class="fw-semibold text-primary" id="reset_username"></p>
<div class="mb-3"><label class="form-label">New Password</label><input type="text" name="new_password" class="form-control" placeholder="Enter new password" required></div>
<div class="mb-3"><label class="form-label">Confirm Password</label><input type="text" name="confirm_password" class="form-control" placeholder="Confirm new password" required></div>
<div class="d-flex justify-content-end">
<button type="submit" class="btn btn-warning me-2"><i class="bi bi-key-fill me-1"></i> Reset</button>
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
document.addEventListener('DOMContentLoaded', function(){
    const success_msg = '<?= $success_msg ?>';
    const usersContainer = document.getElementById('usersContainer');

    if(success_msg) {
        document.getElementById('successOverlay').style.display = 'flex';
        setTimeout(()=>document.getElementById('successOverlay').style.display='none',2000);
    }

    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const resetModal = new bootstrap.Modal(document.getElementById('resetModal'));

    const hideUsers = ()=>usersContainer.style.display='none';
    const showUsers = ()=>usersContainer.style.display='block';

    // Add User
    document.getElementById('addUserBtn').addEventListener('click',()=>{
        document.getElementById('modalTitle').textContent='Add User';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value='';
        hideUsers();
        userModal.show();
    });

    // Edit User
    document.querySelectorAll('.editBtn').forEach(btn=>{
        btn.addEventListener('click',()=>{
            document.getElementById('modalTitle').textContent='Edit User';
            document.getElementById('userId').value=btn.dataset.id;
            document.getElementById('username').value=btn.dataset.username;
            document.getElementById('email').value=btn.dataset.email;
            document.getElementById('password').value=btn.dataset.password;
            document.getElementById('role').value=btn.dataset.role;
            document.getElementById('status').value=btn.dataset.status;
            hideUsers();
            userModal.show();
        });
    });

    // Reset Password
    document.querySelectorAll('.resetBtn').forEach(btn=>{
        btn.addEventListener('click',()=>{
            document.getElementById('reset_user_id').value=btn.dataset.id;
            document.getElementById('reset_username').textContent='Reset password for: '+btn.dataset.username;
            document.getElementById('resetForm').reset();
            hideUsers();
            resetModal.show();
        });
    });

    // Show users container when modal is hidden
    document.getElementById('userModal').addEventListener('hidden.bs.modal', showUsers);
    document.getElementById('resetModal').addEventListener('hidden.bs.modal', showUsers);

    // Toggle Password
    document.querySelectorAll('.password-text .eye-icon').forEach(icon=>{
        icon.addEventListener('click',()=>{
            const td=icon.parentElement;
            const span=td.querySelector('.password-dots');
            const password=td.getAttribute('data-password');
            if(span.textContent==='******'){ span.textContent=password; icon.classList.replace('bi-eye','bi-eye-slash'); }
            else{ span.textContent='******'; icon.classList.replace('bi-eye-slash','bi-eye'); }
        });
    });

    // Live Search
    document.getElementById('userSearch').addEventListener('keyup',function(){
        const search=this.value.toLowerCase();
        document.querySelectorAll('#usersTable tbody tr').forEach(row=>{
            const username=row.querySelector('.username').textContent.toLowerCase();
            row.style.display=username.includes(search)?'':'none';
        });
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
