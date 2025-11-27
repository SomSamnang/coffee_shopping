<?php
session_start();
require_once('../connection/db_connect.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_name = trim($_POST['position_name']);
    $status = intval($_POST['status']);

    if ($position_name) {
        $stmt = $conn->prepare("INSERT INTO positions (position_name, status) VALUES (?, ?)");
        $stmt->bind_param("si", $position_name, $status);

        if ($stmt->execute()) {
            header("Location: ../positions/position_list.php");
            exit;
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Position name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Position</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/add_position.css">

<style>
/* LOADING OVERLAY */
#loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 9999;
}
</style>

</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner-border text-light" role="status"></div>
    <div style="margin-top:10px; text-align:center; color:blue;">
        Please wait...!
    </div>
</div>

<div class="card">
    <h2><i class="bi bi-briefcase me-2"></i>Add New Position</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- FIXED: added id="positionForm" -->
    <form method="post" id="positionForm">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clipboard-plus"></i></span>
                <input type="text" name="position_name" id="position_name" class="form-control" placeholder="Enter position name" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-gradient"><i class="bi bi-check-lg me-1"></i>Add Position</button>
            <a href="../positions/position_list.php" class="btn back-btn"><i class="bi bi-arrow-left-circle me-1"></i>Back</a>
        </div>
    </form>
</div>

<script>
document.getElementById('positionForm').addEventListener('submit', function(e){
    // Show loading overlay immediately
    document.getElementById('loadingOverlay').style.display = 'flex';

    // Delay submit for 4 seconds (4000ms)
    setTimeout(() => {
        e.target.submit();
    }, 10000);  // 4000ms = 4 seconds
});

</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
