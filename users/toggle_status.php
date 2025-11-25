<?php
require_once('connection/db_connect.php'); 

$id = intval($_GET['id']);
if($id > 0){
    $res = $conn->query("SELECT status FROM products WHERE product_id=$id");
    $row = $res->fetch_assoc();
    $new_status = ($row['status']=='active') ? 'inactive' : 'active';
    $conn->query("UPDATE products SET status='$new_status' WHERE product_id=$id");
}
header("Location: product.php");
exit;
?>
