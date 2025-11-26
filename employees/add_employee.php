<?php
session_start();
require_once('../connection/db_connect.php');

$message = "";

// Fetch only ACTIVE positions
$sql = "SELECT * FROM positions WHERE status = 1 ORDER BY position_name ASC";
$positions = $conn->query($sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Auto-generate employee ID
    $last_id_sql = "SELECT id FROM employee ORDER BY no DESC LIMIT 1";
    $last_id_result = $conn->query($last_id_sql);
    if ($last_id_result && $last_id_result->num_rows > 0) {
        $last_row = $last_id_result->fetch_assoc();
        $last_num = (int)substr($last_row['id'], 3); // Remove 'ST-' prefix
        $new_num = $last_num + 1;
    } else {
        $new_num = 1;
    }
    $employee_id = 'ST-' . str_pad($new_num, 3, '0', STR_PAD_LEFT);

    // Sanitize input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $status = $_POST['status'] ?? 'inactive';
    $position = ($status === 'active') ? $_POST['position'] : "";
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $resign_date = !empty($_POST['resign_date']) ? $_POST['resign_date'] : NULL;

    // Handle photo upload
    $photo = 'default.png';
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO employee (id, name, email, phone, position, start_date, resign_date, status, photo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $employee_id, $name, $email, $phone, $position, $start_date, $resign_date, $status, $photo);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../employees/employee_list.php");
    
        exit;
    } else {
        $message = "<div class='alert alert-danger'>❌ Failed to add employee: " . htmlspecialchars($stmt->error) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Employee</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../css/add_employee.css">

</head>
<body>

<div id="loadingOverlay">
    <div class="spinner-border text-light" role="status"></div>
    <div style="margin-top:10px; text-align:center;">
        Adding Employee... Please wait!
    </div>
</div>

<div class="card">
    <div class="card-header fs-4 mb-3">Add New Employee</div>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data" id="employeeForm">
        <img id="preview" src="../uploads/default.png" alt="Preview" class="photo-preview">

        <div class="row g-3 row-cols-responsive">
            <div class="col-md-6 d-flex flex-column">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>

                <label class="form-label mt-2">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>

                <label class="form-label mt-2">Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="Phone number">

                <label class="form-label mt-2">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*" onchange="loadPreview(event)">
            </div>

            <div class="col-md-6 d-flex flex-column">
                <div id="position-wrapper">
                    <label class="form-label">Position</label>
                    <select name="position" id="position" class="form-select">
                        <option value="">-- Select Position --</option>
                        <?php
                        // Re-fetch positions if POST submission removed original pointer
                        $positions->data_seek(0);
                        while ($pos = $positions->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($pos['position_name']); ?>">
                                <?= htmlspecialchars($pos['position_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <label class="form-label mt-2">Start Date</label>
                <input type="date" name="start_date" class="form-control">

                <label class="form-label mt-2">Resign Date</label>
                <input type="date" name="resign_date" class="form-control">

                <label class="form-label mt-2">Status</label>
                <select name="status" id="status" class="form-select" onchange="togglePosition()">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Add Employee</button>
            <a href="../employees/employee_list.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<script>
function togglePosition() {
    const status = document.getElementById("status").value;
    const wrapper = document.getElementById("position-wrapper");
    const position = document.getElementById("position");
    if (status === "inactive") {
        wrapper.style.display = "none";
        position.required = false;
        position.value = "";
    } else {
        wrapper.style.display = "block";
        position.required = true;
    }
}
togglePosition();

function loadPreview(event) {
    document.getElementById('preview').src = URL.createObjectURL(event.target.files[0]);
}

// Show overlay on submit
document.getElementById('employeeForm').addEventListener('submit', function(e){
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';
});
</script>

</body>
</html>
<?php $conn->close(); ?>
