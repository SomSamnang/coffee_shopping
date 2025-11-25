<?php
session_start();
require_once('../connection/db_connect.php');

$id = intval($_GET['id'] ?? 0);

if ($id) {
    $stmt = $conn->prepare("DELETE FROM positions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../positions/position_list.php");
exit;
