<?php
include 'db.php';
$id = intval($_POST['id'] ?? 0);
if(!$id || empty($_FILES['pdf']['name'])){ echo 'bad'; exit; }

$uploadsDir = __DIR__ . '/uploads/';
if(!is_dir($uploadsDir)) mkdir($uploadsDir,0755,true);

$pname = basename($_FILES['pdf']['name']);
$pdfPath = 'uploads/' . time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/','_', $pname);
move_uploaded_file($_FILES['pdf']['tmp_name'], __DIR__ . '/' . $pdfPath);

$stmt = $conn->prepare('UPDATE ebooks SET pdf=? WHERE id=?');
$stmt->bind_param('si',$pdfPath,$id);
if($stmt->execute()) echo 'success'; else echo $conn->error;
?>
