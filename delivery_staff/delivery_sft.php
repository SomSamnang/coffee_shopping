<?php
session_start();
require_once('../connection/db_connect.php');

// --- FETCH POSITIONS INTO ARRAY ---
$positions_result = $conn->query("SELECT * FROM positions ORDER BY position_name ASC");
if(!$positions_result){
    die("Positions Query Failed: " . $conn->error);
}

$positions = [];
while($pos = $positions_result->fetch_assoc()){
    $positions[] = $pos['position_name'];
}

// --- ADD STAFF ---
if(isset($_POST['add_staff'])){
    $name = $_POST['name'];
    $position = $_POST['position'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO delivery_staff (name, position, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $position, $status);
    if($stmt->execute()){
        header("Location: delivery_sft.php");
        exit();
    } else {
        echo "Error adding staff: " . $stmt->error;
    }
}

// --- UPDATE STAFF ---
if(isset($_POST['update_staff'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $position = $_POST['position'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE delivery_staff SET name=?, position=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $position, $status, $id);
    if($stmt->execute()){
        header("Location: delivery_sft.php");
        exit();
    } else {
        echo "Error updating staff: " . $stmt->error;
    }
}

// --- DELETE STAFF ---
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM delivery_staff WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        header("Location: delivery_sft.php");
        exit();
    } else {
        echo "Error deleting staff: " . $stmt->error;
    }
}

// --- FETCH STAFF ---
$result = $conn->query("SELECT * FROM delivery_staff ORDER BY id ASC");
if(!$result){
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Staff Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .modal-header {
            background-color: #0d6efd;
            color: #fff;
        }
        .btn-primary, .btn-warning, .btn-danger {
            border-radius: 8px;
        }
        .card {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Coffee Shop Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Delivery Staff</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../users/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">

    <!-- Add Staff Card -->
    <div class="card mb-4 p-3">
        <h4 class="mb-3">Add New Delivery Staff</h4>
        <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter staff name" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Position</label>
                <select name="position" class="form-select" required>
                    <option value="">-- Select Position --</option>
                    <?php foreach($positions as $pos_name): ?>
                        <option value="<?= htmlspecialchars($pos_name) ?>"><?= htmlspecialchars($pos_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" name="add_staff" class="btn btn-primary w-100">Add Staff</button>
            </div>
        </form>
    </div>

    <!-- Staff Table Card -->
    <div class="card p-3">
        <h4 class="mb-3">Delivery Staff List</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Delivery ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>ST-00<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td>
                            <?php if($row['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this staff?')">Delete</a>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Staff</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Position</label>
                                        <select name="position" class="form-select" required>
                                            <?php foreach($positions as $pos_name): ?>
                                                <option value="<?= htmlspecialchars($pos_name) ?>" <?= $pos_name == $row['position'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($pos_name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?= $row['status']=='active'?'selected':'' ?>>Active</option>
                                            <option value="inactive" <?= $row['status']=='inactive'?'selected':'' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_staff" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
