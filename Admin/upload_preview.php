<?php
include 'db.php';
$id = intval($_POST['id'] ?? 0);
if(!$id || empty($_FILES['preview']['name'])){ echo 'bad'; exit; }

$uploadsDir = __DIR__ . '/uploads/';
if(!is_dir($uploadsDir)) mkdir($uploadsDir,0755,true);

$pname = basename($_FILES['preview']['name']);
$previewPath = 'uploads/' . time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/','_', $pname);
move_uploaded_file($_FILES['preview']['tmp_name'], __DIR__ . '/' . $previewPath);

$stmt = $conn->prepare('UPDATE ebooks SET thumbnail=? WHERE id=?');
$stmt->bind_param('si',$previewPath,$id);
if($stmt->execute()) echo 'success'; else echo $conn->error;
?>
