<?php
include 'auth_check.php';
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch file paths before deleting
$stmt = $conn->prepare("SELECT thumbnail, pdf FROM ebooks WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if ($file) {
    // Delete thumbnail if exists
    if (!empty($file['thumbnail']) && file_exists($file['thumbnail'])) {
        unlink($file['thumbnail']);
    }

    // Delete PDF if exists
    if (!empty($file['pdf']) && file_exists($file['pdf'])) {
        unlink($file['pdf']);
    }

    // Delete DB row
    $stmt = $conn->prepare("DELETE FROM ebooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php?deleted=1");
exit();
