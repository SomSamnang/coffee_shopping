<?php
require_once 'db_connect.php';

$id = intval($_GET['id'] ?? 0);

// Fetch current staff info
$res = $conn->query("SELECT * FROM staff WHERE id=$id");
if ($res->num_rows == 0) {
    header("Location: staff_profile.php");
    exit;
}
$staff = $res->fetch_assoc();

// Fetch active positions
$positions_res = $conn->query("SELECT * FROM positions WHERE status=1 ORDER BY position_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $resign_date = !empty($_POST['resign_date']) ? $_POST['resign_date'] : NULL;
    $status = $_POST['status'] ?? 'inactive';

    $photo = $staff['photo'];

    if (!empty($_FILES['photo']['name'])) {
        if ($photo && file_exists('uploads/'.$photo)) unlink('uploads/'.$photo);
        $photo = time().'_'.basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/'.$photo);
    }

    $stmt = $conn->prepare("UPDATE staff SET name=?, email=?, phone=?, position=?, photo=?, start_date=?, resign_date=?, status=? WHERE id=?");
    $stmt->bind_param(
        "ssssssssi",
        $name,
        $email,
        $phone,
        $position,
        $photo,
        $start_date,
        $resign_date,
        $status,
        $id
    );
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
<title>Edit Staff</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

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

.container { max-width: 600px; width: 100%; }

.card {
    border-radius: 20px;
    padding: 30px;
    background: #fff;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
}

.card-header {
    font-size: 1.6rem;
    font-weight: 700;
    text-align: center;
    color: #6c63ff;
    margin-bottom: 30px;
}

.form-label { font-size: 0.9rem; font-weight: 500; color: #555; }
.form-uniform {
    height: 45px;
    padding: 6px 14px;
    font-size: 0.9rem;
    border-radius: 12px;
    border: 1px solid #ddd;
    box-sizing: border-box;
    transition: 0.3s;
}
.form-uniform:focus {
    border-color: #6c63ff;
    box-shadow: 0 0 10px rgba(108,99,255,0.2);
}

.photo-preview {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ddd;
    display: block;
    margin: 0 auto 20px auto;
    transition: transform 0.3s, box-shadow 0.3s;
}
.photo-preview:hover {
    transform: scale(1.1);
    box-shadow: 0 0 15px rgba(108,99,255,0.3);
}

.btn-primary, .btn-success {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    transition: 0.3s;
}
.btn-success:hover {
    background: #28a745;
    transform: translateY(-2px);
}
.btn-secondary {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    font-size: 0.95rem;
    margin-top: 10px;
}

/* Loading Overlay */
#loadingOverlay {
    position: fixed;
    top:0; left:0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    flex-direction: column;
    color: #c9cdf0ff;
    font-size: 1.2rem;
}
#loadingOverlay .spinner-border {
    width: 3rem;
    height: 3rem;
    margin-bottom: 15px;
    
}

@media (max-width: 768px) { .row-cols-responsive { flex-direction: column; } }
</style>
</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner-border text-light" role="status"></div>
    <div>Please wait...!</div>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">Edit Staff</div>
        <form method="POST" enctype="multipart/form-data" id="staffForm">
            <img id="preview" src="uploads/<?= htmlspecialchars($staff['photo'] ?: 'default.png') ?>" class="photo-preview">

            <div class="row g-3 row-cols-responsive">
                <!-- Left Column -->
                <div class="col-md-6 d-flex flex-column">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control form-uniform" value="<?= htmlspecialchars($staff['name']) ?>" required>

                    <label class="form-label mt-2">Email</label>
                    <input type="email" name="email" class="form-control form-uniform" value="<?= htmlspecialchars($staff['email']) ?>" required>

                    <label class="form-label mt-2">Phone</label>
                    <input type="text" name="phone" class="form-control form-uniform" value="<?= htmlspecialchars($staff['phone']) ?>">

                    <label class="form-label mt-2">Photo</label>
                    <input type="file" name="photo" class="form-control form-uniform" accept="image/*" onchange="loadPreview(event)">
                </div>

                <!-- Right Column -->
                <div class="col-md-6 d-flex flex-column">
                    <label class="form-label">Position</label>
                    <select name="position" id="position" class="form-select form-uniform">
                        <option value="">-- Select Position --</option>
                        <?php while ($pos = $positions_res->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($pos['position_name']) ?>" <?= ($staff['position']==$pos['position_name'])?'selected':'' ?>>
                                <?= htmlspecialchars($pos['position_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label class="form-label mt-2">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-uniform" value="<?= $staff['start_date'] ?? '' ?>">

                    <label class="form-label mt-2">Resign Date</label>
                    <input type="date" name="resign_date" class="form-control form-uniform" value="<?= $staff['resign_date'] ?? '' ?>">

                    <label class="form-label mt-2">Status</label>
                    <select name="status" id="status" class="form-select form-uniform">
                        <option value="active" <?= ($staff['status']??'')==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= ($staff['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Update Staff</button>
                <a href="staff_profile.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
function loadPreview(event) {
    document.getElementById('preview').src = URL.createObjectURL(event.target.files[0]);
}

// Show loading overlay on form submit with 1-second delay
document.getElementById('staffForm').addEventListener('submit', function(e){
    e.preventDefault(); // Prevent immediate form submission
    var overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';

    // Delay submission by 1 second (1000ms)
    setTimeout(() => {
        e.target.submit();
    }, 1000);
});
</script>


</body>
</html>
