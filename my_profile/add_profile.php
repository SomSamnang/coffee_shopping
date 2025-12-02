<?php
session_start();
require_once('../connection/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch positions for dropdown
$positions_result = $conn->query("SELECT * FROM positions WHERE status=1 ORDER BY position_name ASC");
$positions = [];
while ($row = $positions_result->fetch_assoc()) {
    $positions[] = $row;
}

// Auto-generate Employee ID
$result = $conn->query("SELECT employee_id_no FROM profile ORDER BY id DESC LIMIT 1");
$last_id = $result->fetch_assoc();
$employee_id_no = $last_id ? 'ST-' . str_pad((int) str_replace('ST-', '', $last_id['employee_id_no']) + 1, 3, '0', STR_PAD_LEFT) : 'ST-001';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name_kh = $_POST['name_kh'];
    $name_en = $_POST['name_en'];
    $username = $_POST['username'];
    $position = $_POST['position'];
    $position_kh = $_POST['position_kh'] ?? $position;
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $employee_id_no_submitted = $_POST['employee_id_no'];
    $extension = $_POST['extension'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $start_date = $_POST['start_date'];
    $resign_date = empty($_POST['resign_date']) ? NULL : $_POST['resign_date'];
    $marital_status = $_POST['marital_status'];
    $place_of_birth = $_POST['place_of_birth'];
    $current_address = $_POST['current_address'];
    $status = $_POST['status'] ?? 'active';

    // Handle photo upload
    $photo = 'default.png';
    if (!empty($_FILES['photo']['name'])) {
        $photo = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['photo']['name']));
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photo)) {
            $photo = 'default.png';
        }
    }

    $stmt = $conn->prepare("INSERT INTO profile 
        (name_en,name_kh,username,position,position_kh,birth_date,gender,employee_id_no,extension,phone,email,start_date,resign_date,marital_status,place_of_birth,current_address,photo,status) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "ssssssssssssssssss",
        $name_en, $name_kh, $username, $position, $position_kh, $birth_date,
        $gender, $employee_id_no_submitted, $extension, $phone, $email,
        $start_date, $resign_date, $marital_status, $place_of_birth,
        $current_address, $photo, $status
    );

    if ($stmt->execute()) {
        header("Location: my_profile_list.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            max-width: 750px;
            width: 100%;
            border-radius: 20px;
            background: #ffffffdd;
            padding: 30px 35px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .card:hover { transform: translateY(-5px); }
        h5 { font-weight: 600; color: #2575fc; }
        .form-label { font-weight: 500; font-size: 0.85rem; text-transform: uppercase; color: #555; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #e0e6ed; padding: 8px 12px; font-size: 0.9rem; min-height: 38px; }
        .btn-save { background: #2575fc; color: #fff; font-weight: 600; border-radius: 8px; transition: 0.3s; }
        .btn-save:hover { background: #1a5ed8; }
        .btn-secondary { border-radius: 8px; }
        .preview-img-container { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid #2575fc; }
        .photo-preview { width: 100%; height: 100%; object-fit: cover; }
        /* Loading overlay */
        #loadingOverlay {
            position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.5); z-index:9999;
            display: none; justify-content:center; align-items:center; flex-direction:column;
        }
        #loadingOverlay .spinner-border { width: 4rem; height:4rem; color:#fff; }
        #loadingOverlay span { color:#fff; font-weight:600; font-size:1.2rem; margin-top:12px; }
    </style>
</head>
<body>

<!-- Loading overlay -->
<div id="loadingOverlay">
    <div class="spinner-border" role="status"></div>
    <span>Processing...</span>
</div>

<div class="card">
    <h5 class="mb-4 text-center"><i class="bi bi-person-plus-fill me-2"></i>Add New Employee</h5>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="preview-img-container">
                <img src="../uploads/default.png" id="previewImg" class="photo-preview">
            </div>
            <div class="flex-grow-1">
                <label class="form-label">Upload Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewImage(event)">
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Name Khmer</label>
                <input type="text" name="name_kh" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name English</label>
                <input type="text" name="name_en" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Position</label>
                <select name="position" class="form-select" required>
                    <option value="">-- Select Position --</option>
                    <?php foreach($positions as $pos): ?>
                    <option value="<?= htmlspecialchars($pos['position_name']); ?>"><?= htmlspecialchars($pos['position_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Employee ID</label>
                <input type="text" name="employee_id_no" class="form-control" value="<?= $employee_id_no ?>" readonly>
            </div>
            <input type="hidden" name="position_kh" value="">
            <div class="col-md-6">
                <label class="form-label">Birth Date</label>
                <input type="date" name="birth_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="">Select</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Extension</label>
                <input type="text" name="extension" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Resign Date</label>
                <input type="date" name="resign_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Marital Status</label>
                <input type="text" name="marital_status" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Place of Birth</label>
                <input type="text" name="place_of_birth" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Current Address</label>
                <textarea name="current_address" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-save w-100 mt-4">💾 Save New Profile</button>
        <a href="../my_profile/my_profile_list.php" class="btn btn-secondary w-100 mt-2">❌ Cancel</a>
    </form>
</div>

<script>
function previewImage(event) {
    if(event.target.files && event.target.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('previewImg').src = e.target.result;
        reader.readAsDataURL(event.target.files[0]);
    }
}

// Show loading overlay on submit
document.querySelector("form").addEventListener("submit", function() {
    document.getElementById("loadingOverlay").style.display = "flex";
});
</script>

</body>
</html>
