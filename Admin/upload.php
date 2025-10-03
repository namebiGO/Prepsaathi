<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $regular_price = $_POST['regular_price'];
    $offer_price = $_POST['offer_price'];
    $category_id = (int)$_POST['category_id'];

    // Handle file uploads
    $thumbPath = "";
    $pdfPath = "";

    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbName = time() . "_" . basename($_FILES['thumbnail']['name']);
        $thumbPath = "uploads/" . $thumbName;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbPath);
    }

    if (!empty($_FILES['pdf']['name'])) {
        $pdfName = time() . "_" . basename($_FILES['pdf']['name']);
        $pdfPath = "uploads/" . $pdfName;
        move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath);
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO ebooks (title, regular_price, offer_price, category_id, thumbnail, pdf) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddiss", $title, $regular_price, $offer_price, $category_id, $thumbPath, $pdfPath);

    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
