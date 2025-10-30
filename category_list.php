<?php
require_once 'db_connect.php'; 

// Fetch all categories
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Category Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f8fa;
    margin: 0;
    padding: 0;
}

/* Header */
header {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    color: white;
    padding: 15px 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    border-bottom: 2px solid rgba(255,255,255,0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
header h1 {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    background: linear-gradient(90deg, #ffffff, #fdd10d);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Buttons and Search Container */
.nav-buttons a {
    margin-left: 8px;
    text-decoration: none;
    color: #0d6efd;
    background: #ffffff;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 500;
}
.nav-buttons a:hover {
    background: #f1f1f1;
}

/* Search container in header */
.search-container {
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-radius: 12px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 250px;
}
.search-container input {
    border: none;
    outline: none;
    flex: 1;
    font-size: 0.95rem;
}
.search-container i {
    color: #0d6efd;
}

/* Container */
.container {
    max-width: 900px;
    margin: 20px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

/* Table */
.table {
    border-radius: 12px;
    overflow: hidden;
}
.table th, .table td {
    padding: 12px 15px;
    text-align: center;
}
.table th {
    background-color: #f4f4f4;
}
.table tr:nth-child(even) { background-color: #fafafa; }
.table tr:hover { background-color: #f1f7ff; }

/* Action Buttons */
.btn-action {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 14px;
    margin-right: 5px;
    color: white;
    font-weight: 500;
    text-decoration: none;
}
.edit-btn { background-color: #2196F3; }
.edit-btn:hover { background-color: #1976d2; }
.delete-btn { background-color: #f44336; }
.delete-btn:hover { background-color: #c62828; }

/* Hide scrollbar */
html, body {
    height: 100%;
    overflow: auto;
    scrollbar-width: none;
}
body::-webkit-scrollbar { display: none; }

/* Responsive */
@media (max-width:768px) {
    header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .search-container {
        width: 100%;
    }
    .nav-buttons {
        width: 100%;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
}
</style>
</head>
<body>

<header>
    <h1>Category List</h1>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="search-container">
            <input type="text" id="searchBox" placeholder="Search categories...">
            <i class="bi bi-search"></i>
        </div>
        <div class="nav-buttons d-flex gap-1">
            <a href="add_category.php"><i class="bi bi-plus-circle"></i> Add Category</a>
            <a href="index.php"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</header>

<div class="container">
    <div class="table-responsive">
        <table class="table table-striped" id="categoryTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['created_at'] ?? '-' ?></td>
                        <td><?= $row['status'] ?></td>

                        <td>
                            <a class="btn-action edit-btn" href="edit_category.php?id=<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a class="btn-action delete-btn" href="delete_category.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this category?');"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No categories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Live search
document.getElementById('searchBox').addEventListener('keyup', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#categoryTable tbody tr').forEach(row => {
        row.style.display = row.cells[1].innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>

</body>
</html>
