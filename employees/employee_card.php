<?php
session_start();

require_once '../connection/db_connect.php'; 

// Get employee ID from URL
$emp_id = $_GET['id'] ?? null;

// Validate if an ID was provided
if (!$emp_id) {
    // Redirect to the employee list page if no ID is provided
    header("Location: employee_list.php");
    exit;
}

// 1. Prepare SQL statement
// Using COALESCE to ensure a non-null value is always returned for photo
$stmt = $conn->prepare("SELECT id, name, position, email, COALESCE(photo, '') as photo FROM employee WHERE id = ?");

// 2. Bind the ID parameter as a STRING ('s') 
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

// 3. Check if the employee was found
if (!$employee) {
    // Optionally log this attempt
    header("HTTP/1.0 404 Not Found");
    echo "Employee with ID: " . htmlspecialchars($emp_id) . " not found.";
    exit;
}

// Set a placeholder image if the photo field is empty
$photo_source = !empty($employee['photo']) ? '../uploads/' . htmlspecialchars($employee['photo']) : 'https://via.placeholder.com/120/1a73e8/ffffff?text=PHOTO';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee ID Card (ID: <?= htmlspecialchars($employee['id']) ?>)</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/employee_card.css">
</head>
<body>


<div class="id-card">
    <div class="id-card-header">
        <span>RELAX COFFEE CARD</span>
    </div>
    
    <div class="content-area">
        <img src="<?= $photo_source ?>" class="photo" alt="<?= htmlspecialchars($employee['name']) ?> Photo">
        
        <h2><?= htmlspecialchars($employee['name']) ?></h2>
        
        <p class="position"><?= htmlspecialchars($employee['position']) ?></p>
        
        <div class="info-grid">
            <p>ID: <strong class="bg-red"><?= htmlspecialchars($employee['id']) ?></strong></p>
            <p>Email: <strong class="bg-red"><?= htmlspecialchars($employee['email']) ?></strong></p>
            <p>Validity: <strong class="bg-red">2025-2026</strong></p>

        </div>
        
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= urlencode('EMP-ID:' . $employee['id']) ?>" class="qr-code" alt="QR Code">

    </div>
    
    <div class="id-card-footer">
        #123B, Tuol Svay Prey, Beoung Kengkong, Phnom Penh
    </div>
</div>
<script>
window.onload = function() {
    window.print();
};
</script>
</body>
</html>