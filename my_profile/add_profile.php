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
$positions_result->data_seek(0); 

// Auto-generate Employee ID
$result = $conn->query("SELECT employee_id_no FROM profile ORDER BY id DESC LIMIT 1");
$last_id = $result->fetch_assoc();
$employee_id_no = $last_id ? 'ST-' . str_pad((int) str_replace('ST-', '', $last_id['employee_id_no']) + 1, 3, '0', STR_PAD_LEFT) : 'ST-001';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_en = $_POST['name_en'];
    $name_kh = $_POST['name_kh'];
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

    $photo = 'default.png';
    if (!empty($_FILES['photo']['name'])) {
        $photo = time().'_'.preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['photo']['name']));
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo)) {
             $photo = 'default.png'; 
        }
    }

    $stmt = $conn->prepare("INSERT INTO profile 
        (name_en,name_kh,position,position_kh,birth_date,gender,employee_id_no,extension,phone,email,start_date,resign_date,marital_status,place_of_birth,current_address,photo) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssssssss",$name_en,$name_kh,$position,$position_kh,$birth_date,$gender,$employee_id_no_submitted,$extension,$phone,$email,$start_date,$resign_date,$marital_status,$place_of_birth,$current_address,$photo);

    if ($stmt->execute()) {
        echo "<script>alert('Profile Added Successfully'); window.location='add_profile.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error: ".$stmt->error."');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        /* --- General Layout & Sizing --- */
        body { 
            background: #f4f7f9; /* Very light, clean background */
            font-family: 'Poppins', sans-serif;
        }
        .card { 
            max-width: 650px; /* Slightly adjusted width for better 2-col look */
            margin: 25px auto;
            padding: 30px;
            border-radius: 15px; /* Softer rounded corners */
            background: #fff; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.08); /* Minimal shadow for a floating effect */
            border: none;
        }
        
        /* --- Typography & Spacing --- */
        .form-label {
            font-weight: 500; /* Medium weight */
            color: #555; /* Neutral label color */
            margin-bottom: 2px; /* Ultra-tight label margin */
            font-size: 0.8rem; /* Small label font */
            text-transform: uppercase; /* Clean uppercase look */
            letter-spacing: 0.5px;
        }
        .form-control, .form-select {
            border-radius: 6px; /* Sleek, subtle rounded corners */
            border: 1px solid #e0e6ed; /* Light border */
            padding: 7px 10px; /* Reduced input padding */
            font-size: 0.9rem; /* Standard input font */
            min-height: 35px; /* Fixed height for uniformity */
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.15rem rgba(41, 128, 185, 0.15); 
            border-color: #3498db;
        }
        .g-2 {
            --bs-gutter-x: 0.75rem; /* Standard horizontal gap */
            --bs-gutter-y: 0.75rem; /* Standard vertical gap */
        }
        .col-gap {
            margin-bottom: 0; /* Remove redundant bottom margin */
        }

        /* --- Photo Styling --- */
        .photo-area {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }
        .preview-img-container {
            width: 80px; /* Very small photo size */
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #3498db; /* Thin primary border */
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }
        .photo-preview {
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        .photo-file-control {
            flex-grow: 1;
            
        }

        /* --- Button Styling --- */
        .btn-save { 
            background: #3498db; /* Solid Primary Blue */
            color: #fff; 
            font-weight: 600; 
            padding: 8px;
            border-radius: 6px;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
            transition: all 0.2s;
        }
        .btn-save:hover {
            background: #2980b9;
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        .btn-secondary {
            border-radius: 6px;
            background-color: #bdc3c7;
            border-color: #bdc3c7;
            color: #444;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h5 class="mb-4 text-center text-primary border-bottom pb-2">Employee Registration</h5>
        <form method="POST" enctype="multipart/form-data">
            
            <div class="photo-area">
                <div class="preview-img-container">
                    <img src="../uploads/default.png" id="previewImg" class="photo-preview" alt="Profile Photo">
                </div>
                <div class="photo-file-control">
                    <label class="form-label">Upload Photo</label>
                    <input type="file" name="photo" class="form-control col-md-6" accept="image/*" onchange="previewImage(event)">
                </div>
            </div>

            <div class="row g-2">
                
                <div class="col-md-6 col-gap">
                    <label class="form-label">Name Khmer</label>
                    <input type="text" name="name_kh" class="form-control" placeholder="សំណាង" required>
                </div>
                <div class="col-md-6 col-gap">
                    <label class="form-label">Name English</label>
                    <input type="text" name="name_en" class="form-control" placeholder="Samnang" required>
                </div>

                <div class="col-md-6 col-gap">
                    <label class="form-label">Position</label>
                    <select name="position" class="form-select" required>
                        <option value="">-- Select Position --</option>
                        <?php foreach($positions as $pos): ?>
                        <option value="<?= htmlspecialchars($pos['position_name']); ?>"><?= htmlspecialchars($pos['position_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-gap">
                    <label class="form-label">Employee ID</label>
                    <input type="text" name="employee_id_no" class="form-control" value="<?= $employee_id_no ?>" readonly>
                </div>
                <input type="hidden" name="position_kh" value="">

                <div class="col-md-6 col-gap">
                    <label class="form-label">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control" required>
                </div>
                <div class="col-md-6 col-gap">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>

                <div class="col-md-6 col-gap">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="012 345 678" required>
                </div>
                <div class="col-md-6 col-gap">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="example@company.com" required>
                </div>

                <div class="col-md-6 col-gap">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6 col-gap">
                    <label class="form-label">Extension</label>
                    <input type="text" name="extension" class="form-control" placeholder="101">
                </div>

                <div class="col-md-6 col-gap">
                    <label class="form-label">Resign Date</label>
                    <input type="date" name="resign_date" class="form-control">
                </div>
                <div class="col-md-6 col-gap">
                    <label class="form-label">Marital Status</label>
                    <input type="text" name="marital_status" class="form-control" placeholder="Single, Married">
                </div>

                <div class="col-6 col-gap">
                    <label class="form-label">Place of Birth</label>
                    <input type="text" name="place_of_birth" class="form-control" placeholder="Phnom Penh">
                </div>

                <div class="col-6 col-gap">
                    <label class="form-label">Current Address</label>
                    <textarea name="current_address" class="form-control" rows="2" placeholder="Street, Village, Commune, District"></textarea>
                </div>

            </div>
            
            <button type="submit" class="btn btn-save w-100 mt-4">💾 Save New Profile</button>
            <a href="../my_profile/my_profile_list.php" class="btn btn-secondary w-100 mt-2">❌ Cancel</a>
        </form>
    </div>
</div>

<script>
/**
 * Corrected function name and element ID to match the HTML structure (previewImg).
 */
function previewImage(event) {
    if (event.target.files && event.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

</body>
</html>