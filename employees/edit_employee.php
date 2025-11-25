<?php
require_once('../connection/db_connect.php');

// Get staff id from GET (e.g., ST-001)
$employee_id= $_GET['id'] ?? '';

// Fetch current staff info
$stmt = $conn->prepare("SELECT * FROM employee WHERE id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    header("Location: ../employees/employee_list.php");
    exit;
}
$employee = $res->fetch_assoc();
$stmt->close();

// Fetch active positions
$positions_res = $conn->query("SELECT * FROM positions WHERE status=1 ORDER BY position_name ASC");

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $resign_date = !empty($_POST['resign_date']) ? $_POST['resign_date'] : NULL;
    $status = $_POST['status'] ?? 'inactive';

    $photo = $employee['photo'] ?: 'default.png';

    if (!empty($_FILES['photo']['name'])) {
        if ($photo && file_exists('../uploads/'.$photo)) unlink('../uploads/'.$photo);
        $photo = time().'_'.basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/'.$photo);
    }

    $stmt = $conn->prepare("UPDATE employee SET name=?, email=?, phone=?, position=?, photo=?, start_date=?, resign_date=?, status=? WHERE id=?");
    $stmt->bind_param(
        "sssssssss",
        $name,
        $email,
        $phone,
        $position,
        $photo,
        $start_date,
        $resign_date,
        $status,
        $employee_id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: ../employees/employee_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Employees</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/coffee_shops/css/employee_list.css">

</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner-border text-light" role="status"></div>
    <div>Please wait...!</div>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">Edit Employees</div>

        <!-- Show No & ID -->
        <div class="staff-id-info">
            No: <?= $employee['no'] ?> | ID: <?= htmlspecialchars($employee['id']) ?>
        </div>

        <form method="POST" enctype="multipart/form-data" id="employeeForm">
            <img id="preview" src="../uploads/<?= htmlspecialchars($employee['photo'] ?: 'default.png') ?>" class="photo-preview">

            <div class="row g-3 row-cols-responsive">
                <!-- Left Column -->
                <div class="col-md-6 d-flex flex-column">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control form-uniform" value="<?= htmlspecialchars($employee['name']) ?>" required>

                    <label class="form-label mt-2">Email</label>
                    <input type="email" name="email" class="form-control form-uniform" value="<?= htmlspecialchars($employee['email']) ?>" required>

                    <label class="form-label mt-2">Phone</label>
                    <input type="text" name="phone" class="form-control form-uniform" value="<?= htmlspecialchars($employee['phone']) ?>">

                    <label class="form-label mt-2">Photo</label>
                    <input type="file" name="photo" class="form-control form-uniform" accept="image/*" onchange="loadPreview(event)">
                </div>

                <!-- Right Column -->
                <div class="col-md-6 d-flex flex-column">
                    <label class="form-label">Position</label>
                    <select name="position" id="position" class="form-select form-uniform">
                        <option value="">-- Select Position --</option>
                        <?php while ($pos = $positions_res->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($pos['position_name']) ?>" <?= ($employee['position']==$pos['position_name'])?'selected':'' ?>>
                                <?= htmlspecialchars($pos['position_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label class="form-label mt-2">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-uniform" value="<?= $employee['start_date'] ?? '' ?>">

                    <label class="form-label mt-2">Resign Date</label>
                    <input type="date" name="resign_date" class="form-control form-uniform" value="<?= $employee['resign_date'] ?? '' ?>">

                    <label class="form-label mt-2">Status</label>
                    <select name="status" id="status" class="form-select form-uniform">
                        <option value="active" <?= ($employee['status']??'')==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= ($employee['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Update Employees</button>
                <a href="../employees/employee_list.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
function loadPreview(event) {
    document.getElementById('preview').src = URL.createObjectURL(event.target.files[0]);
}

// Show loading overlay on form submit
document.getElementById('employeeForm').addEventListener('submit', function(e){
    e.preventDefault();
    var overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';
    setTimeout(() => { e.target.submit(); }, 1000);
});
</script>

</body>
</html>
