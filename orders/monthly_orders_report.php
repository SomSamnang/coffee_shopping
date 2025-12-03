<?php
session_start();
require_once('../connection/db_connect.php');

// Get selected month/year or default to current
$selected_month = $_POST['month'] ?? date('m');
$selected_year  = $_POST['year'] ?? date('Y');

// Totals
$totals_sql = "SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount),0) AS total_sales
               FROM orders
               WHERE YEAR(created_at)=? AND MONTH(created_at)=?";
$stmt_totals = $conn->prepare($totals_sql);
$stmt_totals->bind_param("ii", $selected_year, $selected_month);
$stmt_totals->execute();
$totals = $stmt_totals->get_result()->fetch_assoc();

// Top Product
$top_sql = "SELECT p.name, SUM(oi.quantity) AS sold
            FROM order_items oi
            LEFT JOIN products p ON p.product_id = oi.product_id
            LEFT JOIN orders o ON o.id = oi.order_id
            WHERE YEAR(o.created_at)=? AND MONTH(o.created_at)=?
            GROUP BY oi.product_id
            ORDER BY sold DESC
            LIMIT 1";
$stmt_top = $conn->prepare($top_sql);
$stmt_top->bind_param("ii", $selected_year, $selected_month);
$stmt_top->execute();
$top = $stmt_top->get_result()->fetch_assoc();
$top_name = $top['name'] ?? 'N/A';
$top_qty  = $top['sold'] ?? 0;

// Orders
$orders_sql = "SELECT * FROM orders WHERE YEAR(created_at)=? AND MONTH(created_at)=? ORDER BY created_at DESC";
$stmt_orders = $conn->prepare($orders_sql);
$stmt_orders->bind_param("ii", $selected_year, $selected_month);
$stmt_orders->execute();
$orders = $stmt_orders->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthly Orders Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Body & Scrollbar */
body {
    background-color: #f4f6f8;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* hide horizontal scrollbar */
}
body::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

/* Navbar */
.navbar {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    position: sticky;
    top: 0;
    z-index: 1000;
    font-weight: 500;
    background: linear-gradient(135deg, #1e3c72, #2a5298, #00b09b, #96c93d);
}
.navbar-brand {
    font-weight: 700;
    font-size: 1.6rem;
    position: relative;
    overflow: hidden;
    color: #fff !important;
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
.navbar-nav .nav-link {
    font-weight: 500;
    transition: all 0.3s ease;
}
.navbar-nav .nav-link:hover {
    transform: translateY(-2px);
    color: #ffd700 !important;
}

/* Table */
.table th, .table td {
    vertical-align: middle;
    text-align: center;
}
.table-hover tbody tr:hover {
    background-color: #e0f7ff;
}
.table-responsive {
    overflow-x: auto;
}

/* Headings */
h2 {
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 20px;
}

/* Buttons & forms */
.no-print {
    margin-bottom: 15px;
}

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }
    #printableArea, #printableArea * {
        visibility: visible;
    }
    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark mb-2">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-4" href="#">
      <i class="bi bi-cup-straw me-2"></i> Coffee Shop Monthly Report
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-3">
          <a class="nav-link text-white" href="../orders/orders.php"><i class="bi bi-bag-check-fill me-1"></i> Orders</a>
        </li>
        <li class="nav-item me-3">
          <a class="nav-link text-white" href="../orders/orders_report.php"><i class="bi bi-graph-up-arrow me-1"></i> Reports</a>
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

  <!-- Select Month/Year -->
  <form method="post" class="row g-2 mb-5 no-print">
    <div class="col-auto">
      <select name="month" class="form-select">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= $m==$selected_month?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-auto">
      <select name="year" class="form-select">
        <?php for($y=date('Y');$y>=2020;$y--): ?>
          <option value="<?= $y ?>" <?= $y==$selected_year?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Show</button>
    </div>
  </form>

  <!-- Actions -->
  <div class="d-flex justify-content-between no-print mb-4">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
    <form action="export_orders_csv.php" method="post">
      <input type="hidden" name="report_type" value="month">
      <input type="hidden" name="month" value="<?= $selected_month ?>">
      <input type="hidden" name="year" value="<?= $selected_year ?>">
      <button type="submit" class="btn btn-success"><i class="bi bi-download"></i> Export CSV</button>
    </form>
  </div>

  <!-- Printable Area -->
  <div id="printableArea">

    <h2>📄 Orders for Monthly Report - <?= date('F', mktime(0,0,0,$selected_month,1)) ?> <?= $selected_year ?></h2>
    
    <div class="table-responsive">
      <table class="table table-bordered table-hover mt-3">
        <thead>
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
          <?php if($orders->num_rows>0): ?>
            <?php while($order = $orders->fetch_assoc()): 
              $order_id=$order['id'];
              $items_sql="SELECT oi.*, p.name, p.price
                          FROM order_items oi
                          LEFT JOIN products p ON p.product_id=oi.product_id
                          WHERE oi.order_id=$order_id";
              $items=$conn->query($items_sql);
              $first=true;
            ?>
            <?php if($items->num_rows>0): ?>
              <?php while($item=$items->fetch_assoc()): ?>
                <tr>
                  <?php if($first): ?>
                    <td rowspan="<?= $items->num_rows ?>">#000<?= $order['id'] ?></td>
                    <td rowspan="<?= $items->num_rows ?>"><?= date('Y-m-d h:i A', strtotime($order['created_at'])) ?></td>
                  <?php endif; ?>
                  <td><?= htmlspecialchars($item['name'] ?? '-') ?></td>
                  <td><?= $item['quantity'] ?? 0 ?></td>
                  <td><?= htmlspecialchars($item['sugar_level'] ?? '-') ?></td>
                  <td>$<?= number_format($item['price'] ?? 0,2) ?></td>
                  <td>$<?= number_format(($item['price'] ?? 0)*($item['quantity'] ?? 0),2) ?></td>
                  <?php if($first): ?>
                    <td rowspan="<?= $items->num_rows ?>">$<?= number_format($order['total_amount'],2) ?></td>
                  <?php endif; ?>
                </tr>
              <?php $first=false; endwhile; ?>
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
            <tr><td colspan="8">No orders found for this month.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Summary -->
    <div class="mt-1">
      <span class="me-3"><strong>Total Orders:</strong> <?= $totals['total_orders'] ?></span>
      <span class="me-3"><strong>Total Sales:</strong> $<?= number_format($totals['total_sales'],2) ?></span>
      <span class="me-3"><strong>Top Product:</strong> <?= $top_name ?> (<?= $top_qty ?>)</span>
    </div>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
