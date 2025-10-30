<?php
require_once 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: categories_list.php?msg=Category deleted successfully");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Invalid ID";
}
?>
