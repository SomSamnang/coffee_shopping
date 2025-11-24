<?php
require_once 'db_connect.php';

// Fetch only ACTIVE positions
$sql = "SELECT * FROM positions WHERE status = 1 ORDER BY position_name ASC";
$positions = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'] ?? 'inactive';
    $position = ($status === 'active') ? $_POST['position'] : "";
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $resign_date = !empty($_POST['resign_date']) ? $_POST['resign_date'] : NULL;

    $photo = 'default.png';
    if (!empty($_FILES['photo']['name'])) {
        $photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
    }

    $stmt = $conn->prepare("INSERT INTO staff (name, email, phone, position, start_date, resign_date, status, photo)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $email, $phone, $position, $start_date, $resign_date, $status, $photo);
    $stmt->execute();
    $stmt->close();

    header("Location: staff_profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Staff</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #667eea, #764ba2);
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px;
}

/* Compact Card */
.card {
    border-radius: 20px;
    padding: 30px;
    background: #fff;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
}

/* Header */
.card-header {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    color: #6c63ff;
    margin-bottom: 20px;
}

/* Status Selector on Top */
#status {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 10px;
    font-size: 0.95rem;
    margin-bottom: 20px;
    width: 100%;
}
#status:focus {
    border-color: #6c63ff;
    box-shadow: 0 0 10px rgba(108,99,255,0.25);
}

/* Inputs */
.form-label {
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 4px;
    color: #555;
}
.form-control, .form-select {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 10px 12px;
    font-size: 0.9rem;
    transition: all 0.3s;
}
.form-control:focus, .form-select:focus {
    border-color: #6c63ff;
    box-shadow: 0 0 12px rgba(108,99,255,0.2);
}

/* Photo Preview */
.photo-preview {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    margin: 0 auto 15px auto;
    border: 3px solid #ddd;
    transition: 0.3s;
}
.photo-preview:hover {
    transform: scale(1.05);
    border-color: #6c63ff;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, #6c63ff, #5047d6);
    border: none;
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #fff;
    transition: all 0.3s;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}
.btn-secondary {
    width: 100%;
    padding: 10px;
    border-radius: 12px;
    font-size: 0.9rem;
    margin-top: 8px;
    border: 1px solid #6c63ff;
    color: #ffffffff;
    background: #5a5246ff;
    transition: all 0.3s;
}
.btn-secondary:hover {
    background: #6c63ff;
    color: #0c2387ff;
}

/* Flex columns */
.row-cols-responsive {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.row-cols-responsive .col-md-6 {
    flex: 1;
}
@media (max-width: 480px) {
    .row-cols-responsive {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<div class="card">
    <div class="card-header">Add New Staff</div>

  

    <form method="POST" enctype="multipart/form-data" id="staffForm">
        <img id="preview" src="uploads/default.png" alt="" class="photo-preview">

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
                        <?php while ($pos = $positions->fetch_assoc()): ?>
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
    <select name="status" id="status" form="staffForm" onchange="togglePosition()">
        <option value="active" selected>Active</option>
        <option value="inactive">Inactive</option>
    </select>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Add Staff</button>
            <a href="staff_profile.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<script>
function togglePosition() {
    var status = document.getElementById("status").value;
    var wrapper = document.getElementById("position-wrapper");
    var position = document.getElementById("position");
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
</script>

</body>
</html>
