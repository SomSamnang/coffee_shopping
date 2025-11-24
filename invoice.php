<?php
require_once 'db_connect.php';
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = null;
$items = [];

if ($order_id > 0) {
    // Fetch order
    $stmt = $conn->prepare("SELECT id, total_amount, created_at FROM orders WHERE id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if ($order) {
        // Fetch order items including sugar_level
        $stmt = $conn->prepare("
            SELECT oi.quantity, oi.item_price, (oi.quantity*oi.item_price) AS total_price,
                   p.name AS product_name, oi.sugar_level
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id=?
        ");
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
<link rel="stylesheet" href="style/invoice.css">
</head>
<body>

<?php if($order): ?>
<div class="invoice-box">
    <div class="invoice-header">
        <div>
            <h1>INVOICE</h1>
            <p>#ID: <?= str_pad($order['id'],4,'0',STR_PAD_LEFT) ?></p>
            <p>Date: <?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></p>
        </div>
        <div class="text-end">
            <h5 class="text-primary fw-bold">☕ RELAX COFFEE </h5>
            <p>No.123,Street 45, Toul Kork, Phnom Penh</p>
            <p>Samnang168@cfe-Shopping.com</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Sugar</th>
                    <th class="text-end">Unit</th>
                    
                    <th class="text-center">Qty</th>
                    
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $i => $it): ?>
                <tr>
                    
                    <td><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?>. <?= htmlspecialchars($it['product_name']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($it['sugar_level']) ?></td>
                    <td class="text-end">$<?= number_format($it['item_price'],2) ?></td>
                    
                    <td class="text-center"><?= $it['quantity'] ?></td>
                    
                    <td class="text-end">$<?= number_format($it['total_price'],2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">TOTAL:</th>
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

<script>
window.onload = function() { window.print(); };
window.onafterprint = function() { window.location.href = "orders.php"; };
</script>

<?php else: ?>
<div class="alert alert-danger text-center">❌ Invoice not found.</div>
<a href="orders.php" class="btn btn-secondary mt-3">Back to Orders</a>
<?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>
