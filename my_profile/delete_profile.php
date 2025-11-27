<?php
session_start();
require_once('../connection/db_connect.php');

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: my_profile_list.php"); exit(); }

// Delete profile
$stmt = $conn->prepare("DELETE FROM profile WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: my_profile_list.php");
exit();
