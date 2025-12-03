<?php
session_start();
require_once('../connection/db_connect.php'); // your DB connection

// --- Add Staff ---
if(isset($_POST['add_staff'])){
    $name = $_POST['name'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("INSERT INTO delivery_staff (name, status) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $status);
    $stmt->execute();
    header("Location: delivery_staff.php");
}

// --- Update Staff ---
if(isset($_POST['update_staff'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE delivery_staff SET name=?, status=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $status, $id);
    $stmt->execute();
    header("Location: delivery_staff.php");
}

// --- Delete Staff ---
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM delivery_staff WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: delivery_staff.php");
}

// --- Fetch All Staff ---
$result = $conn->query("SELECT * FROM delivery_staff ORDER BY id ASC");
?>
