<?php
require_once('../connection/db_connect.php');
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($order_id<=0){ echo json_encode(['success'=>false]); exit; }

$sql = "SELECT p.name, oi.quantity, oi.item_price, oi.sugar_level
        FROM order_items oi
        LEFT JOIN products p ON p.product_id = oi.product_id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$order_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0;
while($row=$res->fetch_assoc()){
    $items[] = [
        'name'=>$row['name'],
        'qty'=>$row['quantity'],
        'price'=>$row['item_price'],
        'sugar'=>$row['sugar_level']
    ];
    $total += $row['quantity']*$row['item_price'];
}
$stmt->close();
$conn->close();
echo json_encode(['success'=>true,'items'=>$items,'total'=>$total]);
?>
