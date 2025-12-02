<?php
session_start();
require_once('../connection/db_connect.php');

$id = $_GET['id'] ?? null;
if (!$id) { 
    header("Location: my_profile_list.php"); 
    exit(); 
}

// Fetch user profile
$stmt = $conn->prepare("SELECT * FROM profile WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) { die("Profile not found"); }

// Fetch positions for dropdown
$positions_result = $conn->query("SELECT * FROM positions WHERE status=1 ORDER BY position_name ASC");
$positions = [];
while ($row = $positions_result->fetch_assoc()) {
    $positions[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $name_en = $_POST['name_en'];
    $name_kh = $_POST['name_kh'];
    $position = $_POST['position'];
    $position_kh = $_POST['position_kh'];
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $employee_id_no = $_POST['employee_id_no'];
    $extension = $_POST['extension'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $start_date = $_POST['start_date'];
    $resign_date_input = trim($_POST['resign_date']);
    $resign_date = (strtolower($resign_date_input) === 'working' || empty($resign_date_input)) ? NULL : $resign_date_input;
    $marital_status = $_POST['marital_status'];
    $place_of_birth = $_POST['place_of_birth'];
    $current_address = $_POST['current_address'];
    $status = $_POST['status'] ?? 'active';

    // Handle photo upload
    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $photo = time().'_'.basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }

    // Update profile
    $stmt = $conn->prepare("
        UPDATE profile SET 
            username=?, name_en=?, name_kh=?, position=?, position_kh=?, birth_date=?, gender=?,
            employee_id_no=?, extension=?, phone=?, email=?,
            start_date=?, resign_date=?, marital_status=?, place_of_birth=?, current_address=?, photo=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "ssssssssssssssssssi",
        $username, $name_en, $name_kh, $position, $position_kh, $birth_date, $gender,
        $employee_id_no, $extension, $phone, $email,
        $start_date, $resign_date, $marital_status, $place_of_birth, $current_address, $photo, $status, $id
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
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
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
            max-width: 700px;
            width: 100%;
            border-radius: 20px;
            background: #ffffffdd;
            padding: 30px 35px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .card:hover { transform: translateY(-5px); }
        h4 { font-weight: 600; color: #2575fc; text-align: center; margin-bottom: 25px; }
        .form-label { font-weight: 500; font-size: 0.85rem; text-transform: uppercase; color: #555; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #e0e6ed; padding: 8px 12px; font-size: 0.9rem; min-height: 38px; }
        .btn-primary { background: #2575fc; border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .btn-primary:hover { background: #1a5ed8; }
        .preview-img { width: 140px; height: 140px; object-fit: cover; border-radius: 50%; border: 3px solid #2575fc; margin-bottom: 10px; }
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
    <h4>Edit Profile</h4>
    <form method="POST" enctype="multipart/form-data">
        <center>
            <img src="../uploads/<?= $user['photo'] ?: 'default.png' ?>" id="previewImg" class="preview-img">
            <input type="file" name="photo" accept="image/*" class="form-control mt-2" onchange="previewImage(event)">
        </center>

        <div class="row mt-3">
            <div class="col-md-6 mb-2">
                <label>Name EN</label>
                <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($user['name_en']) ?>" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Name KH</label>
                <input type="text" name="name_kh" class="form-control" value="<?= htmlspecialchars($user['name_kh']) ?>" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Position</label>
                <select name="position" class="form-select" required>
                    <option value="">-- Select Position --</option>
                    <?php foreach($positions as $pos): ?>
                        <option value="<?= htmlspecialchars($pos['position_name']) ?>" <?= $user['position']==$pos['position_name']?'selected':'' ?>>
                            <?= htmlspecialchars($pos['position_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-2">
                <label>Position KH</label>
                <input type="text" name="position_kh" class="form-control" value="<?= htmlspecialchars($user['position_kh']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Birth Date</label>
                <input type="date" name="birth_date" class="form-control" value="<?= $user['birth_date'] ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="">Select</option>
                    <option value="Male" <?= $user['gender']=="Male"?"selected":"" ?>>Male</option>
                    <option value="Female" <?= $user['gender']=="Female"?"selected":"" ?>>Female</option>
                </select>
            </div>
            <div class="col-md-6 mb-2">
                <label>Employee ID No</label>
                <input type="text" name="employee_id_no" class="form-control" value="<?= htmlspecialchars($user['employee_id_no']) ?>" readonly>
            </div>
            <div class="col-md-6 mb-2">
                <label>Extension</label>
                <input type="text" name="extension" class="form-control" value="<?= htmlspecialchars($user['extension']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $user['start_date'] ?>" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>Resign Date</label>
                <input type="text" name="resign_date" class="form-control" value="<?= htmlspecialchars($user['resign_date'] ?: 'working') ?>" placeholder="Enter date or type 'working'">
            </div>
            <div class="col-md-6 mb-2">
                <label>Marital Status</label>
                <input type="text" name="marital_status" class="form-control" value="<?= htmlspecialchars($user['marital_status']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Place of Birth</label>
                <input type="text" name="place_of_birth" class="form-control" value="<?= htmlspecialchars($user['place_of_birth']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
                    <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12 mb-2">
                <label>Current Address</label>
                <textarea name="current_address" class="form-control"><?= htmlspecialchars($user['current_address']) ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">Update Profile</button>
         <a href="../my_profile/my_profile_list.php" class="btn btn-secondary w-100 mt-2">❌ Cancel</a>
    </form>
</div>

<script>
function previewImage(event) {
    if(event.target.files && event.target.files[0]) {
        document.getElementById('previewImg').src = URL.createObjectURL(event.target.files[0]);
    }
}

// Show loading overlay on submit
document.querySelector("form").addEventListener("submit", function() {
    let overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';
});
</script>

</body>
</html>
