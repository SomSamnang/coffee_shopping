<?php
require_once 'db_connect.php';

$id = intval($_GET['id'] ?? 0);

// Get current photo
$res = $conn->query("SELECT photo FROM staff WHERE id=$id");
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    if ($row['photo'] && file_exists('uploads/'.$row['photo'])) {
        unlink('uploads/'.$row['photo']);
    }
}

// Delete staff
$conn->query("DELETE FROM staff WHERE id=$id");
header("Location: index.php");
?>
