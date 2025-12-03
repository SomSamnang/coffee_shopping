<?php
session_start();
require_once('../connection/db_connect.php');

// -------------------- Today's Totals --------------------
$totals_today_sql = "SELECT 
        COUNT(*) AS total_orders_today,
        COALESCE(SUM(total_amount),0) AS total_sales_today
    FROM orders
    WHERE DATE(created_at) = CURDATE()";
$totals_today = $conn->query($totals_today_sql)->fetch_assoc();

// -------------------- Top Product Today --------------------
$top_sql = "SELECT p.name, SUM(oi.quantity) AS sold_today
            FROM order_items oi
            LEFT JOIN products p ON p.product_id = oi.product_id
            LEFT JOIN orders o ON o.id = oi.order_id
            WHERE DATE(o.created_at) = CURDATE()
            GROUP BY oi.product_id
            ORDER BY sold_today DESC
            LIMIT 1";
$top = $conn->query($top_sql)->fetch_assoc();
$top_name = $top['name'] ?? 'N/A';
$top_qty  = $top['sold_today'] ?? 0;

// -------------------- Today's Orders --------------------
$orders_sql = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC";
$orders = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Today's Orders Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background-color: #f8f9fa; }
.navbar { box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.nav-link { font-weight: 500; font-size: 1rem; }
.navbar-brand { font-weight: bold; font-size: 1.5rem; letter-spacing: 1px; }
.table-hover tbody tr:hover { background-color: #e9f5ff; }
.table th, .table td { vertical-align: middle; text-align: center; }
.no-print { margin-bottom: 15px; }
h2 { font-weight: bold; margin-bottom: 20px; color: #0d6efd; }
@media print { .no-print, .navbar { display: none !important; } }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">☕ Coffee Shop Orders Report</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>



      <!-- User Dropdown -->
      <?php if(isset($_SESSION['username'])): ?>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" style="color:yellow"></i> <?= htmlspecialchars($_SESSION['username']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="../my_profile/my_profile.php"><i class="bi bi-person me-2 text-primary"></i> Profile</a></li>
            <li><a class="dropdown-item" href="../orders/orders_history.php"><i class="bi bi-clock-history me-2 text-warning"></i> History Orders</a></li>
          
            <li>
                <a class="dropdown-item" href="../orders/monthly_orders_report.php">
                    <i class="bi bi-calendar3-week me-2 text-info"></i> Monthly Reports
                </a>        
               <!-- Orders -->
            <li>
                <a class="dropdown-item" href="../orders/orders.php">
                    <i class="bi bi-basket me-2 text-success"></i> Orders
                </a>
            </li>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><a class="dropdown-item" href="../home/index.php"><i class="bi bi-house-door me-2 text-success"></i> Home</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../users/logout.php"><i class="fa fa-power-off me-2"></i></i> Logout</a></li>
          </ul>
        </li>
      </ul>
      <?php else: ?>
      <a class="btn btn-outline-light btn-sm" href="../users/login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container">

  <!-- Actions -->
  <div class="d-flex justify-content-between no-print">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
    <form action="export_orders_csv.php" method="post">
      <button type="submit" class="btn btn-success"><i class="bi bi-download"></i> Export CSV</button>
    </form>
  </div>

  <h2>📄 Today's Detailed Orders Report</h2>

  <!-- Orders Table -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover mt-3">
      <thead class="table-primary">
        <tr>
          <th>Order ID</th>
          <th>Order Date</th>
          <th>Product</th>
          <th>Qty</th>
          <th>Sugar</th>
          <th>Price</th>
          <th>Subtotal</th>
          <th>Total Amount</th>
        </tr>
      </thead>
      <tbody>
      <?php if($orders->num_rows > 0): ?>
          <?php while($order = $orders->fetch_assoc()): ?>
              <?php
              $order_id = $order['id'];
              $items_sql = "SELECT oi.*, p.name, p.price
                            FROM order_items oi
                            LEFT JOIN products p ON p.product_id = oi.product_id
                            WHERE oi.order_id = $order_id";
              $items = $conn->query($items_sql);
              $first = true;
              ?>
              <?php if($items->num_rows > 0): ?>
                  <?php while($item = $items->fetch_assoc()): ?>
                  <tr>
                      <?php if($first): ?>
                          <td rowspan="<?= $items->num_rows ?>">#000<?= $order['id'] ?></td>
                          <td rowspan="<?= $items->num_rows ?>"><?= date('Y-m-d h:i A', strtotime($order['created_at'])) ?></td>
                      <?php endif; ?>
                      <td><?= htmlspecialchars($item['name'] ?? '-') ?></td>
                      <td><?= $item['quantity'] ?? 0 ?></td>
                      <td><?= htmlspecialchars($item['sugar_level'] ?? '-') ?></td>
                      <td>$<?= number_format($item['price'] ?? 0,2) ?></td>
                      <td>$<?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0),2) ?></td>
                      <?php if($first): ?>
                          <td rowspan="<?= $items->num_rows ?>">$<?= number_format($order['total_amount'],2) ?></td>
                      <?php endif; ?>
                  </tr>
                  <?php $first = false; ?>
                  <?php endwhile; ?>
              <?php else: ?>
                  <tr>
                      <td>#000<?= $order['id'] ?></td>
                      <td><?= date('Y-m-d h:i A', strtotime($order['created_at'])) ?></td>
                      <td colspan="5">No items found</td>
                      <td>$<?= number_format($order['total_amount'],2) ?></td>
                  </tr>
              <?php endif; ?>
          <?php endwhile; ?>
      <?php else: ?>
          <tr><td colspan="8">No orders found for today.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
