<?php
require_once 'db_connect.php'; // your database connection

$message = '';

// Fetch all categories
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Category Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        a.add-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 18px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        a.add-btn:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        tr:hover {
            background-color: #f1f7ff;
        }

        td {
            color: #555;
        }

        .btn {
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            margin-right: 5px;
            transition: 0.3s;
        }

        .edit-btn {
            background-color: #2196F3;
        }

        .edit-btn:hover {
            background-color: #1976d2;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .delete-btn:hover {
            background-color: #c62828;
        }

        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            background-color: #e0ffe0;
            color: #2e7d32;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Category List</h1>

    <?php if($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <a class="add-btn" href="add_category.php">+ Add Category</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= isset($row['created_at']) ? $row['created_at'] : '-' ?></td>
                    <td>
                        <a class="btn edit-btn" href="edit_category.php?id=<?= $row['id'] ?>">Edit</a>
                        <a class="btn delete-btn" href="delete_category.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">No categories found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
