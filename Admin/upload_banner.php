<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot_id = (int)$_POST['slot_id'];
    $news_text = trim($_POST['news_text']);

    if (!empty($news_text)) {
        $stmt = $conn->prepare("UPDATE banners SET news_text = ? WHERE id = ?");
        $stmt->bind_param("si", $news_text, $slot_id);
        $stmt->execute();
        $stmt->close();

        header("Location: banner.php?success=1");
        exit;
    } else {
        header("Location: banner.php?error=1");
        exit;
    }
}
