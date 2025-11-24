<?php
require_once 'db_connect.php';

$id = intval($_GET['id'] ?? 0);

// Fetch current staff info
$res = $conn->query("SELECT * FROM staff WHERE id=$id");
if ($res->num_rows == 0) {
    header("Location: index.php");
    exit;
}
$staff = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];

    $photo = $staff['photo']; // keep existing photo by default

    // Handle file upload
    if (!empty($_FILES['photo']['name'])) {
        // Delete old photo if exists
        if ($photo && file_exists('uploads/'.$photo)) {
            unlink('uploads/'.$photo);
        }
        $photo = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
    }

    $stmt = $conn->prepare("UPDATE staff SET name=?, email=?, phone=?, position=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $position, $photo, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Staff</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <h2>Edit Staff</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($staff['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($staff['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($staff['phone']) ?>">
        </div>
        <div class="mb-3">
            <label>Position</label>
            <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($staff['position']) ?>">
        </div>
        <div class="mb-3">
            <label>Photo</label><br>
            <?php if($staff['photo'] && file_exists('uploads/'.$staff['photo'])): ?>
                <img src="uploads/<?= $staff['photo'] ?>" width="80" height="80" class="mb-2"><br>
            <?php endif; ?>
            <input type="file" name="photo" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Update Staff</button>
        <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</div>

</body>
</html>
