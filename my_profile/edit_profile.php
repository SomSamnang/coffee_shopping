<?php
session_start();
require_once('../connection/db_connect.php');

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: my_profile_list.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM profile WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) { die("Profile not found"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
    $resign_date = empty($_POST['resign_date']) ? NULL : $_POST['resign_date'];
    $marital_status = $_POST['marital_status'];
    $place_of_birth = $_POST['place_of_birth'];
    $current_address = $_POST['current_address'];

    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $photo = time().'_'.basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }

    $stmt = $conn->prepare("
        UPDATE profile SET 
        name_en=?, name_kh=?, position=?, position_kh=?, birth_date=?, gender=?,
        employee_id_no=?, extension=?, phone=?, email=?,
        start_date=?, resign_date=?, marital_status=?, place_of_birth=?, current_address=?, photo=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssssssssssssssssi",
        $name_en, $name_kh, $position, $position_kh, $birth_date, $gender,
        $employee_id_no, $extension, $phone, $email,
        $start_date, $resign_date, $marital_status, $place_of_birth, $current_address, $photo, $id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Profile Updated'); window.location='my_profile_list.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error: ".$stmt->error."');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .card {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .preview-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #2575fc;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="card">
    <h4 class="mb-3">Edit Profile</h4>
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
                <label>Position</label>
                <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($user['position']) ?>" required>
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
                <select name="gender" class="form-control" required>
                    <option value="">Select</option>
                    <option <?= $user['gender']=="Male"?"selected":"" ?>>Male</option>
                    <option <?= $user['gender']=="Female"?"selected":"" ?>>Female</option>
                </select>
            </div>
            <div class="col-md-6 mb-2">
                <label>Employee ID No</label>
                <input type="text" name="employee_id_no" class="form-control" value="<?= htmlspecialchars($user['employee_id_no']) ?>" required>
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
                <input type="date" name="resign_date" class="form-control" value="<?= $user['resign_date'] ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Marital Status</label>
                <input type="text" name="marital_status" class="form-control" value="<?= htmlspecialchars($user['marital_status']) ?>">
            </div>
            <div class="col-md-6 mb-2">
                <label>Place of Birth</label>
                <input type="text" name="place_of_birth" class="form-control" value="<?= htmlspecialchars($user['place_of_birth']) ?>">
            </div>
            <div class="col-12 mb-2">
                <label>Current Address</label>
                <textarea name="current_address" class="form-control"><?= htmlspecialchars($user['current_address']) ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">Update Profile</button>
    </form>
</div>

<script>
function previewImage(event) {
    document.getElementById('previewImg').src = URL.createObjectURL(event.target.files[0]);
}
</script>

</body>
</html>
