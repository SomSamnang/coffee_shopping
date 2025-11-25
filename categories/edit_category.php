<?php
require_once('../connection/db_connect.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) die("Invalid category ID.");

$message = "";

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) die("Category not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $status = $_POST['status'];

    if (empty($name)) {
        $message = "Category name cannot be empty.";
    } else {
        $update = $conn->prepare("UPDATE categories SET name = ?, status = ? WHERE id = ?");
        $update->bind_param("ssi", $name, $status, $id);
        if ($update->execute()) {
            // ✅ Redirect directly to category list page with success message
            header("Location: ../categories/category_list.php?msg=updated");
            exit;
        } else {
            $message = "Error updating category: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/edit_category.css">
</head>
<body>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border" role="status"></div>
    <div class="loading-text">Updating, please wait...</div>
</div>

<div class="form-card">
    <div class="form-header">Edit Category</div>

    <div class="form-body">
        <?php if($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" id="updateForm">
            <label for="name" class="form-label">Category Name *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>

            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button type="submit" class="btn btn-submit">Update</button>
            <a href="../categories/category_list.php" class="btn btn-back">Back</a>
        </form>
    </div>
</div>

<script>
document.getElementById("updateForm").addEventListener("submit", function() {
    document.getElementById("loadingOverlay").style.display = "flex";
});

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
window.addEventListener("load", function() {
    const overlay = document.createElement("div");
    overlay.className = "loading-overlay";
    overlay.style.display = "flex";
    overlay.innerHTML = `
        <div class="spinner-border" role="status"></div>
        <div class="loading-text">Update successful! Redirecting...</div>
    `;
    document.body.appendChild(overlay);

    setTimeout(() => {
        window.location.href = "../categories/category_list.php";
    }, 1000); // 1 seconds delay
});
<?php endif; ?>
</script>


</body>
</html>
