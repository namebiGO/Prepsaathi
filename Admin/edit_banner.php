<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$banner = $result->fetch_assoc();

if (!$banner) {
    die("Banner not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $imagePath = $banner['image'];

    if (!empty($_FILES['image']['name'])) {
        $imgName = time() . "_" . basename($_FILES['image']['name']);
        $imagePath = "uploads/banners/" . $imgName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("UPDATE banners SET name=?, image=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $imagePath, $id);

    if ($stmt->execute()) {
        header("Location: banner.php?updated=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<?php include 'partials/header.php'; ?>
<div class="container">

    <div class="content">
        <div class="edit-container card">
            <h1>Edit Banner</h1>
            <form action="" method="POST" enctype="multipart/form-data" class="form-ui">

                <div class="form-group">
                    <label>Banner Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($banner['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Upload New Banner (optional)</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if (!empty($banner['image'])): ?>
                        <div class="preview">
                            <img src="<?= $banner['image']; ?>" alt="banner">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">Update Banner</button>
            </form>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>

<style>
    .edit-container {
        max-width: 800px;
        margin: 40px auto;
        background: #fff;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .edit-container h1 {
        font-size: 24px;
        margin-bottom: 20px;
        text-align: center;
        color: #333;
    }

    .form-ui .form-group {
        margin-bottom: 18px;
    }

    .form-ui label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
        color: #444;
    }

    .form-ui input,
    .form-ui select {
        width: 100%;
        padding: 10px 12px;
        font-size: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.2s;
    }

    .form-ui input:focus,
    .form-ui select:focus {
        border-color: #007bff;
    }

    .preview {
        margin-top: 10px;
    }

    .preview img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .btn-primary {
        background: #007bff;
        color: #fff;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: #0056b3;
    }
</style>