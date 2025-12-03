<?php
session_start();
require_once('../connection/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

// --- ADD DELIVERY ---
if (isset($_POST['add_delivery'])) {
    $order_id = $_POST['order_id'];
    $delivery_name = $_POST['delivery_name'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO deliveries (order_id, delivery_name, address, delivery_status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("iss", $order_id, $delivery_name, $address);
    $stmt->execute();
    $stmt->close();
    header("Location: delivery.php");
    exit();
}

// --- UPDATE DELIVERY ---
if (isset($_POST['update_delivery'])) {
    $id = $_POST['delivery_id'];
    $order_id = $_POST['order_id'];
    $delivery_name = $_POST['delivery_name'];
    $address = $_POST['address'];
    $status = $_POST['delivery_status'];

    $stmt = $conn->prepare("UPDATE deliveries SET order_id=?, delivery_name=?, address=?, delivery_status=? WHERE id=?");
    $stmt->bind_param("isssi", $order_id, $delivery_name, $address, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: delivery.php");
    exit();
}

// --- DELETE DELIVERY ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM deliveries WHERE id=$id");
    header("Location: delivery.php");
    exit();
}

// Fetch deliveries
$deliveries = $conn->query("
    SELECT d.*, o.total_amount, o.created_at AS order_date 
    FROM deliveries d
    JOIN orders o ON d.order_id = o.id
    ORDER BY d.id DESC
");

// Fetch orders for dropdown (Add Delivery)
$orders = $conn->query("SELECT * FROM orders WHERE id NOT IN (SELECT order_id FROM deliveries)");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Cool Gradient Navbar */
        .navbar-custom {
            background: linear-gradient(135deg, #1e3c72, #2a5298, #00b09b, #96c93d);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-weight: 500;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            color: #fff !important;
            position: relative;
            overflow: hidden;
        }
        .navbar-brand::after {
            content: '';
            position: absolute;
            top: 0;
            left: -75%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg, rgba(255,255,255,0.4), rgba(255,255,255,0));
            transform: skewX(-25deg);
            transition: all 0.5s ease;
        }
        .navbar-brand:hover::after {
            left: 125%;
        }
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            transform: translateY(-2px);
            color: #ffd700 !important;
        }
        h2 {
            color: #0015d5ff;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="p-4">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="bi bi-truck me-2"></i>Delivery Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-3">
          <a class="nav-link text-white" href="../orders/orders.php"><i class="bi bi-bag-check-fill me-1"></i> Orders</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-warning fw-bold" href="#" id="userDropdown" role="button" 
             data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5 me-1"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="../my_profile/my_profile.php"><i class="bi bi-person-badge me-2 text-primary"></i> Profile</a></li>
            <li><a class="dropdown-item" href="../orders/orders_history.php"><i class="bi bi-clock-history me-2 text-success"></i> History Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger fw-bold" href="../users/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <h2 class="mb-3">Delivery List</h2>

    <!-- ADD DELIVERY FORM -->
    <form method="POST" class="mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="order_id" class="form-select" required>
                    <option value="">Select Order</option>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>">Order #<?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?> - $<?= $row['total_amount'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="delivery_name" class="form-control" placeholder="Delivery Name" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="address" class="form-control" placeholder="Address" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="add_delivery" class="btn btn-primary w-100">Add Delivery</button>
            </div>
        </div>
    </form>

    <!-- DELIVERY LIST -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Delivery Name</th>
                <th>Amount</th>
                <th>Order Date</th>
                <th>Address</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $deliveries->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td>#<?= str_pad($row['order_id'],3,'0',STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($row['delivery_name']) ?></td>
                <td>$<?= $row['total_amount'] ?></td>
                <td><?= $row['order_date'] ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= $row['delivery_status'] ?></td>
                <td>
                    <!-- Edit button triggers modal -->
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <!-- Delete button -->
                    <a href="delivery.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this delivery?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>

            <!-- EDIT MODAL -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel<?= $row['id'] ?>">Edit Delivery #<?= $row['id'] ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="delivery_id" value="<?= $row['id'] ?>">

                            <!-- Editable Order ID -->
                            <div class="mb-2">
                                <label>Order ID</label>
                                <select name="order_id" class="form-select" required>
                                    <?php
                                    echo "<option value='{$row['order_id']}' selected>Order #".str_pad($row['order_id'],3,'0',STR_PAD_LEFT)."</option>";
                                    $available_orders = $conn->query("SELECT * FROM orders WHERE id NOT IN (SELECT order_id FROM deliveries) AND id != {$row['order_id']}");
                                    while ($o = $available_orders->fetch_assoc()) {
                                        echo "<option value='{$o['id']}'>Order #".str_pad($o['id'],3,'0',STR_PAD_LEFT)." - $".$o['total_amount']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label>Delivery Name</label>
                                <input type="text" name="delivery_name" class="form-control" value="<?= htmlspecialchars($row['delivery_name']) ?>" required>
                            </div>
                            <div class="mb-2">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" required>
                            </div>
                            <div class="mb-2">
                                <label>Status</label>
                                <select name="delivery_status" class="form-select" required>
                                    <option value="Pending" <?= $row['delivery_status']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Delivered" <?= $row['delivery_status']=='Delivered'?'selected':'' ?>>Delivered</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="update_delivery" class="btn btn-success">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
              </div>
            </div>

        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
