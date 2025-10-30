<?php
require_once 'db_connect.php';
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = null;
$items = [];

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT id, total_amount, created_at FROM orders WHERE id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        $stmt = $conn->prepare("SELECT oi.quantity, oi.item_price, (oi.quantity*oi.item_price) AS total_price, p.name AS product_name 
                                FROM order_items oi 
                                JOIN products p ON oi.product_id=p.product_id 
                                WHERE oi.order_id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $res_items = $stmt->get_result();
        while ($row = $res_items->fetch_assoc()) $items[] = $row;
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #<?= str_pad($order_id,4,'0',STR_PAD_LEFT) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 20px;
    color: #333;
}
.invoice-box {
    max-width: 450px;
    height: 490px;
    margin: 0 auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.05);
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #ddd;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
.invoice-header h1 {
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 5px;
    font-size: 1.6rem;
}
.invoice-header p {
    margin: 0;
    color: #555;
    font-size: 0.9rem;
}
.table th, .table td {
    padding: 12px;
    vertical-align: middle;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f8f9fa;
}
.final-total {
    font-weight: 700;
    font-size: 1.4rem;
    color: #198754;
}
.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}
.no-print {
    text-align: left;
    margin-top: 20px;
}
.no-print .btn {
    margin: 5px;
}
@media print {
    .no-print { display: none; }
}
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
<!-- Buttons Outside Invoice -->
<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">🖨️ Print Invoice</button>
    <a href="orders.php" class="btn btn-secondary">New Order</a>
    <a href="index.php" class="btn btn-outline-secondary">Back</a>
</div>
<?php if($order): ?>
<div class="invoice-box">
    <div class="invoice-header">
        <div>
            <h1>INVOICE</h1>
            <p>#ID: <?= str_pad($order['id'],4,'0',STR_PAD_LEFT) ?></p>
            <p>Date: <?= date('Y-m-d H:i:s',strtotime($order['created_at'])) ?></p>
        </div>
        <div class="text-end">
            <h5 class="text-primary fw-bold">☕COFFEE SHOPPING</h5>
            <p>123 Coffee Lane</p>
            <p>Samnang@dailyShopping.com</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-end">Unit</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $i=>$it): ?>
                <tr>
                    <td><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?>. <?= htmlspecialchars($it['product_name']) ?></td>
                    <td class="text-end">$<?= number_format($it['item_price'],2) ?></td>
                    <td class="text-center"><?= $it['quantity'] ?></td>
                    <td class="text-end">$<?= number_format($it['total_price'],2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">TOTAL:</th>
                    <th class="text-end final-total">$<?= number_format($order['total_amount'],2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-4 text-center">
       <p class="mb-1">Thank you for your business!</p>
    <p class="mb-0">We hope to see you again ☕</p>
    </div>
</div>
<?php else: ?>
<div class="alert alert-danger text-center">❌ Invoice not found.</div>
<?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>
