<?php
session_start();
require_once 'db_connect.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: position_list.php");
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
            header("Location: position_list.php");
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
</head>
<body>
<div class="container mt-5">
    <h2>Edit Position</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="position_name" class="form-label">Position Name</label>
            <input type="text" name="position_name" id="position_name" class="form-control" value="<?= htmlspecialchars($position['position_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="1" <?= $position['status'] == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $position['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update Position</button>
        <a href="position_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
