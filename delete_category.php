<?php
require_once 'db_connect.php';

// Get ID from URL and validate
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid category ID.");
}

// Check if category exists
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    die("Category not found.");
}

// Delete category
$delete = $conn->prepare("DELETE FROM categories WHERE id = ?");
$delete->bind_param("i", $id);
if ($delete->execute()) {
    // Redirect with success message
    header("Location: category_list.php?msg=Category deleted successfully");
    exit;
} else {
    die("Error deleting category: " . $conn->error);
}
