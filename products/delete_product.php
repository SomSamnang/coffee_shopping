<?php
require_once('../connection/db_connect.php');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) die("Invalid Product ID");

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    header("Location: ..products/product.php?msg=Product deleted successfully");
    exit;
} else {
    echo "Error deleting product: " . $conn->error;
}
?>
