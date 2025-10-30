<?php
require_once('db_connect.php');

// --- Fetch all orders with products ---
$sql = "
SELECT o.id AS order_id, o.total_amount, o.created_at,
       GROUP_CONCAT(CONCAT(p.name,' x', oi.quantity) SEPARATOR ', ') AS products
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.product_id = oi.product_id
GROUP BY o.id
ORDER BY o.created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Error fetching orders: " . $conn->error);
}

// --- Fetch total orders and total sales ---
$totals_sql = "
SELECT COUNT(*) AS total_orders,
       COALESCE(SUM(total_amount),0) AS total_sales,
       COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE() THEN total_amount ELSE 0 END),0) AS today_sales
FROM orders
";

$totals_result = $conn->query($totals_sql);
if (!$totals_result) {
    die("Error fetching totals: " . $conn->error);
}
$totals = $totals_result->fetch_assoc();

// --- Fetch top-selling product today ---
$top_sql = "
SELECT p.name, SUM(oi.quantity) AS sold_today
FROM order_items oi
LEFT JOIN products p ON p.product_id = oi.product_id
LEFT JOIN orders o ON o.id = oi.order_id
WHERE DATE(o.created_at) = CURDATE()
GROUP BY oi.product_id
ORDER BY sold_today DESC
LIMIT 1
";

$top_result = $conn->query($top_sql);
if (!$top_result) {
    die("Error fetching top-selling product: " . $conn->error);
}

$top_product = $top_result->fetch_assoc();
$top_name = $top_product ? $top_product['name'] : 'N/A';
$top_qty  = $top_product ? $top_product['sold_today'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders History</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { 
    background: #f0f2f5; 
    font-family: 'Poppins', sans-serif; 
    margin:0; 
}

/* Navbar */
.navbar { 
    background: linear-gradient(90deg,#0d6efd,#6610f2); 
    box-shadow:0 3px 8px rgba(0,0,0,0.1);
}
.navbar-brand { font-weight:700; color:#fff; font-size:1.4rem; }

/* Summary Cards */
.summary-cards { 
    display:flex; 
    gap:20px; 
    flex-wrap:wrap; 
    margin-bottom:25px;
}
.card-summary { 
    flex:1; 
    min-width:180px; 
    height: 170px;
    border-radius:12px; 
    padding:20px; 
    color:#fff; 
    box-shadow:0 4px 12px rgba(0,0,0,0.1); 
    transition: transform 0.2s, box-shadow 0.2s;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
}
.card-summary:hover { transform: translateY(-3px); box-shadow:0 6px 16px rgba(0,0,0,0.15);}
.card-summary h5 { font-weight:700; margin-bottom:10px; font-size:1.1rem; }
.card-summary p { font-size:1.5rem; font-weight:700; margin:0; }

/* Definite Colors */
.card-orders { background:#198754; }   /* Green */
.card-total { background:#0d6efd; }    /* Blue */
.card-today { background:#ffc107; color:#222; }  /* Yellow */
.card-top { background:#dc3545; }      /* Red */

/* Orders Table */
.table td:first-child { text-align:center; font-weight:500; width:50px; }
.table tbody tr:hover { background:#e9f0ff; }
.table-responsive { box-shadow:0 2px 12px rgba(0,0,0,0.08); border-radius:12px; overflow:hidden; }
/* Hide scrollbar but allow scroll */
html, body {
    height: 100%;
    overflow: auto;
    scrollbar-width: none;
}
body::-webkit-scrollbar {
    display: none;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">☕ Orders History</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-2">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a></li>
      
                <li class="nav-item"><a class="nav-link active" href="orders_history.php"><i class="bi bi-clock-history"></i> Orders History</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4">

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card-summary card-orders">
            <h5>Total Orders</h5>
            <p><?= $totals['total_orders'] ?></p>
        </div>
        <div class="card-summary card-total">
            <h5>Total Sales</h5>
            <p>$<?= number_format($totals['total_sales'],2) ?></p>
        </div>
        <div class="card-summary card-today">
            <h5>Today Sales</h5>
            <p>$<?= number_format($totals['today_sales'],2) ?></p>
        </div>
        <div class="card-summary card-top">
            <h5>Top Selling Today</h5>
            <p><?= htmlspecialchars($top_name) ?> (<?= $top_qty ?>)</p>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Products</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): 
                    $count = 1;
                    while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++ ?></td>
                        <td><?= htmlspecialchars($row['products']) ?></td>
                        <td>$<?= number_format($row['total_amount'],2) ?></td>
                        <td><?= date('Y-m-d H:i:s', strtotime($row['created_at'])) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" class="text-center">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
