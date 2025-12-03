<?php
session_start();
require_once('../connection/db_connect.php');

// Current user info
$currentUser = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

// --- Fetch all orders ---
$sql = "SELECT o.id AS order_id, o.total_amount, o.created_at FROM orders o ORDER BY o.created_at DESC";
$result = $conn->query($sql);
if (!$result) die("Error fetching orders: " . $conn->error);

// --- Totals ---
$totals_sql = "SELECT COUNT(*) AS total_orders,
                       COALESCE(SUM(total_amount),0) AS total_sales,
                       COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE() THEN total_amount ELSE 0 END),0) AS today_sales
                FROM orders";
$totals_result = $conn->query($totals_sql);
if (!$totals_result) die("Error fetching totals: " . $conn->error);
$totals = $totals_result->fetch_assoc();

// --- Top product today ---
$top_sql = "SELECT p.name, SUM(oi.quantity) AS sold_today
            FROM order_items oi
            LEFT JOIN products p ON p.product_id = oi.product_id
            LEFT JOIN orders o ON o.id = oi.order_id
            WHERE DATE(o.created_at) = CURDATE()
            GROUP BY oi.product_id
            ORDER BY sold_today DESC
            LIMIT 1";
$top_result = $conn->query($top_sql);
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
<link rel="stylesheet" href="../css/orders_history.css">
</head>
<body>

<!-- Navbar with User Dropdown -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
<div class="container-fluid">
    <a class="navbar-brand" href="#">☕ Orders History</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
<ul class="navbar-nav align-items-center">

    <?php if($currentUser): ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1 text-warning"></i>
            <?= htmlspecialchars($currentUser) ?>
        </a>

        <ul class="dropdown-menu dropdown-menu-end">

            <!-- Profile -->
            <li>
                <a class="dropdown-item" href="../my_profile/my_profile.php">
                    <i class="bi bi-person me-2 text-primary"></i> Profile
                </a>
            </li>

            <!-- Orders -->
            <li>
                <a class="dropdown-item" href="../orders/orders.php">
                    <i class="bi bi-basket me-2 text-success"></i> Orders
                </a>
            </li>
            <!-- orders_report.php -->
            <li>
                <a class="dropdown-item" href="../orders/orders_report.php">
                    <i class="bi bi-file-earmark-text me-2 text-info"></i> Orders Report
                </a>

            </li>
   
            <li>
                <a class="dropdown-item" href="../delivery/delivery.php">
                    <i class="bi bi-truck me-2 text-secondary"></i> Delivery
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="../orders/export_orders_csv.php">
                    <i class="bi bi-file-earmark-arrow-down me-2 text-success"></i> Export Orders CSV
                </a>
            </li>
       
        
            <li><hr class="dropdown-divider"></li>

            <!-- Logout -->
            <li>
                <a class="dropdown-item text-danger" href="../users/logout.php">
                    <i class="bi bi-box-arrow-right me-2 text-danger"></i> Logout
                </a>
            </li>

        </ul>
    </li>

    <?php else: ?>
    <li class="nav-item">
        <a class="nav-link btn btn-outline-light btn-sm" href="../users/login.php">
            <i class="bi bi-box-arrow-in-right me-2 text-success"></i> Login
        </a>
    </li>
    <?php endif; ?>

</ul>

    </div>
</div>
</nav>

<div class="container my-4">
<!-- Summary Cards -->
<div class="summary-cards">
    <div class="card-summary card-orders"><h5>Total Orders</h5><p><?= $totals['total_orders'] ?></p></div>
    <div class="card-summary card-total"><h5>Total Sales</h5><p>$<?= number_format($totals['total_sales'],2) ?></p></div>
    <div class="card-summary card-today"><h5>Today Sales</h5><p>$<?= number_format($totals['today_sales'],2) ?></p></div>
    <div class="card-summary card-top"><h5>Top Selling Today</h5><p><?= htmlspecialchars($top_name) ?> (<?= $top_qty ?>)</p></div>
</div>

<!-- Orders Table -->
<div class="table-wrapper mx-auto" style="max-width:900px;">
<table class="table table-hover table-bordered mb-0 align-middle text-center">
<thead>
<tr>
    <th style="width:100px; color:yellow;">Order ID</th>
    <th style="width:120px; color:yellow;">Total Amount</th>
    <th style="width:150px; color:yellow;">Order Date</th>
</tr>
</thead>
<tbody>
<?php if ($result && $result->num_rows>0):
    while($row = $result->fetch_assoc()): ?>
<tr>
    <td><a href="#" class="order-link" data-id="<?= $row['order_id'] ?>">#000<?= $row['order_id'] ?></a></td>
    <td>$<?= number_format($row['total_amount'],2) ?></td>
    <td><?= date('Y-m-d h:i A', strtotime($row['created_at'])) ?></td>

</tr>
<?php endwhile; else: ?>
<tr><td colspan="3">No orders found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="orderModalBody">Loading...</div>
    <div class="modal-footer">
        <strong id="modalTotal" class="me-auto"></strong>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Handle order click
document.querySelectorAll('.order-link').forEach(link => {
    link.addEventListener('click', function(e){
        e.preventDefault();
        const orderId = this.dataset.id;
        const modalBody = document.getElementById('orderModalBody');
        const modalTotal = document.getElementById('modalTotal');
        modalBody.innerHTML = 'Loading...';
        modalTotal.textContent = '';

        fetch('../orders/order_detail.php?id=' + orderId)
        .then(res => res.json())
        .then(data => {
            if(data.success){
                let html = "<table><thead><tr><th>Product</th><th>Qty</th><th>Sugar</th><th>Price</th></tr></thead><tbody>";
                data.items.forEach(item=>{
                    html += `<tr>
                        <td>${item.name}</td>
                        <td>${item.qty}</td>
                        <td>${item.sugar}</td>
                        <td>$${(item.price*item.qty).toFixed(2)}</td>
                    </tr>`;
                });
                html += "</tbody></table>";
                modalBody.innerHTML = html;
                modalTotal.textContent = "Total: $" + parseFloat(data.total).toFixed(2);
            } else { modalBody.innerHTML = "Error loading order details."; }
        }).catch(err => modalBody.innerHTML = 'Error loading order details');

        const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        orderModal.show();
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
