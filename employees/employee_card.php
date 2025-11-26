<?php
require_once '../connection/db_connect.php';

$emp_id = $_GET['id'] ?? null;
if (!$emp_id) {
    header("Location: add_employee.php");
    exit;
}

$stmt = $conn->prepare("SELECT id,name,position,email,photo FROM employee WHERE id=?");
$stmt->bind_param("i",$emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "Employee not found.";
    exit;
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee ID Card</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

body {
font-family: 'Inter', sans-serif;
background: linear-gradient(120deg, #74ebd5, #ACB6E5);
display: flex;
justify-content: center;
align-items: center;
height: 100vh;
margin: 0;
}

.id-card {
width: 280px;
height: 430px;
border-radius: 20px;
background: linear-gradient(180deg, #baff60ff 0%, #ffa1a1ff 100%);
padding: 20px;
box-shadow: 0 10px 25px rgba(0,0,0,0.2);
display: flex;
flex-direction: column;
align-items: center;
position: relative;
overflow: hidden;
transition: transform 0.3s;
}

.id-card:hover {
transform: scale(1.05);
}

.id-card-header {
width: 100%;
height: 50px;
background: linear-gradient(90deg, #4b7bec, #34c3eb);
border-radius: 15px 15px 0 0;
display: flex;
align-items: center;
justify-content: center;
color: #fff;
font-weight: 700;
font-size: 16px;
margin-bottom: 15px;
box-shadow: 0 3px 5px rgba(0,0,0,0.1);
}

.id-card .photo {
width: 120px;
height: 120px;
border-radius: 50%;
object-fit: cover;
border: 3px solid #4b7bec;
margin-bottom: 15px;
box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.id-card h2 {
font-size: 20px;
font-weight: 700;
margin-bottom: 5px;
text-align: center;
color: #2100c7ff;
}

.id-card .position {
font-size: 14px;
font-weight: 600;
color: #00af20ff;
margin-bottom: 10px;
text-align: center;
}

.id-card .info1 {
font-size: 13px;
color: #000000ff;
margin-bottom: 4px;
text-align: center;
}
.id-card .info {
font-size: 13px;
color: #1100ffff;
margin-bottom: 4px;
text-align: center;
}

.id-card .address {
font-size: 9px;
color: #000000ff;
margin-top: 5px;
text-align: center;
}

.id-card-footer {
font-size: 10px;
text-align: center;
position: absolute;
bottom: 0px;
width: 100%;
color: #0800ffff;
border-radius: 0 0 15px 15px ;
background-color: #4bec53ff;
}

@media print {
body { background: #fff; display: block; }
.id-card { box-shadow:none; margin:auto; page-break-after:always; }
} </style>

</head>
<body>

<div class="id-card">
    <div class="id-card-header">Relax Coffee Card</div>
    <img src="../uploads/<?= htmlspecialchars($employee['photo'] ?: 'default.png') ?>" class="photo" alt="Employee Photo">
    <h2><?= htmlspecialchars($employee['name']) ?></h2>
    <p class="position"><?= htmlspecialchars($employee['position']) ?></p>
    <p class="info">ID: <?= $employee['id'] ?></p>
    <p class="info1">Email: <?= htmlspecialchars($employee['email']) ?></p>
    <p class="address">#123B,Tuol Svay Prey,Beoung kengkong,Phnom Penh</p>
    <div class="id-card-footer">2025-2026</div>
</div>

<script>
window.onload = function() {
    window.print();
};
</script>

</body>
</html>
