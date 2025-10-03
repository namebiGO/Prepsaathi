<?php
include 'auth_check.php';
include 'db.php';

// ADD category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
    header("Location: categories.php");
    exit();
}

// DELETE category
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: categories.php");
    exit();
}

// GET all categories
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Categories</title>
<link rel="stylesheet" href="style.css">
<style>
    .container { padding: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table, th, td { border: 1px solid #ccc; }
    th, td { padding: 10px; text-align: left; }
    form input[type=text] { padding: 8px; width: 300px; }
    form button { padding: 8px 12px; background: #28a745; color: white; border: none; cursor: pointer; }
    form button:hover { background: #218838; }
    .delete-btn { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
    .delete-btn:hover { background: #c82333; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">PDFs</a>
    <a href="categories.php" class="active">Categories</a>
    <a href="#">Revenue</a>
    <a href="logout.php" style="text-decoration:none; padding:8px 12px; background:#dc3545; color:white; border-radius:4px;">Logout</a>
</div>

<div class="main-content">
    <h1>Manage Categories</h1>

    <!-- Add New Category Form -->
    <form action="" method="POST">
        <input type="text" name="name" placeholder="Category Name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <!-- Category List -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><a href="categories.php?delete=<?= $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this category?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
