<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

// Handle Mark as Delivered
if (isset($_GET['mark_delivered'])) {
    $delivery_id = $_GET['mark_delivered'];
    $conn->query("UPDATE deliveries SET delivery_status='Delivered' WHERE id=$delivery_id");
    header("Location: orders.php");
}

// Handle Add Delivery via POST
if (isset($_POST['add_delivery'])) {
    $order_id = $_POST['order_id'];
    $address = $_POST['address'];
    $stmt = $conn->prepare("INSERT INTO deliveries (order_id, address) VALUES (?, ?)");
    $stmt->bind_param("is", $order_id, $address);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php");
}

// Fetch orders with delivery info
$sql = "
SELECT o.id AS order_id, o.total_amount, o.created_at,
       d.id AS delivery_id, d.delivery_status, d.address AS delivery_address
FROM orders o
LEFT JOIN deliveries d ON o.id = d.order_id
ORDER BY o.id DESC
";
$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders & Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h2>Orders List</h2>

    <!-- Orders Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Total Amount</th>
                <th>Order Date</th>
                <th>Delivery Address</th>
                <th>Delivery Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td>$<?= $row['total_amount'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['delivery_address'] ?? '-' ?></td>
                <td><?= $row['delivery_status'] ?? 'Not Added' ?></td>
                <td>
                    <?php if (!$row['delivery_id']): ?>
                        <!-- Button to open Add Delivery modal -->
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeliveryModal" data-orderid="<?= $row['order_id'] ?>">Add Delivery</button>
                    <?php elseif ($row['delivery_status'] == 'Pending'): ?>
                        <a href="orders.php?mark_delivered=<?= $row['delivery_id'] ?>" class="btn btn-success btn-sm">Mark as Delivered</a>
                    <?php else: ?>
                        <span class="text-success">Delivered</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Delivery Modal -->
<div class="modal fade" id="addDeliveryModal" tabindex="-1" aria-labelledby="addDeliveryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addDeliveryModalLabel">Add Delivery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="order_id" id="modalOrderId">
          <div class="mb-3">
              <label for="address" class="form-label">Delivery Address</label>
              <input type="text" name="address" id="address" class="form-control" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_delivery" class="btn btn-primary">Add Delivery</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pass order ID to modal
var addDeliveryModal = document.getElementById('addDeliveryModal');
addDeliveryModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var orderId = button.getAttribute('data-orderid');
  document.getElementById('modalOrderId').value = orderId;
});
</script>

</body>
</html>
