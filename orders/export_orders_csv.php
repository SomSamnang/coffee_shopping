<?php
session_start();
require_once('../connection/db_connect.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=todays_orders.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Order ID','Order Date','Product','Qty','Sugar','Price','Subtotal','Total Amount']);

$orders_sql = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC";
$orders = $conn->query($orders_sql);

if($orders->num_rows > 0){
    while($order = $orders->fetch_assoc()){
        $order_id = $order['id'];
        $items_sql = "SELECT oi.*, p.name, p.price
                      FROM order_items oi
                      LEFT JOIN products p ON p.product_id = oi.product_id
                      WHERE oi.order_id = $order_id";
        $items = $conn->query($items_sql);

        if($items->num_rows > 0){
            while($item = $items->fetch_assoc()){
                fputcsv($output, [
                    '#000'.$order['id'],
                    $order['created_at'],
                    $item['name'] ?? '-',
                    $item['quantity'] ?? 0,
                    $item['sugar_level'] ?? '-',
                    $item['price'] ?? 0,
                    ($item['price'] ?? 0) * ($item['quantity'] ?? 0),
                    $order['total_amount']
                ]);
            }
        } else {
            fputcsv($output, [
                '#000'.$order['id'],
                $order['created_at'],
                'No items',
                0,
                '-',
                0,
                0,
                $order['total_amount']
            ]);
        }
    }
}
fclose($output);
exit;
?>
