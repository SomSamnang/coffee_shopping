<?php
session_start();
require_once('../connection/db_connect.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: ../positions/position_list.php");
    exit;
}

// Fetch existing position
$stmt = $conn->prepare("SELECT * FROM positions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$position = $result->fetch_assoc();
$stmt->close();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_name = trim($_POST['position_name']);
    $status = intval($_POST['status']);

    if ($position_name) {
        $stmt = $conn->prepare("UPDATE positions SET position_name = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sii", $position_name, $status, $id);
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
<title>Edit Position</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/edit_position.css">

<style>
/* Loading Overlay */
#loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: none; /* hidden by default */
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
    <h2><i class="bi bi-pencil-square me-2"></i>Edit Position</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" id="editPositionForm">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clipboard-plus"></i></span>
                <input type="text" name="position_name" id="position_name" class="form-control" value="<?= htmlspecialchars($position['position_name']) ?>" placeholder="Enter position name" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="1" <?= $position['status'] == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $position['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-gradient"><i class="bi bi-check-lg me-1"></i>Update Position</button>
            <a href="../positions/position_list.php" class="btn back-btn"><i class="bi bi-arrow-left-circle me-1"></i>Back</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Show loading overlay and delay submission (4 seconds)
document.getElementById('editPositionForm').addEventListener('submit', function(e){
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';

    // Delay form submission for 4 seconds
    setTimeout(() => {
        e.target.submit();
    }, 4000); // 4000ms = 4 seconds
});
</script>

</body>
</html>
