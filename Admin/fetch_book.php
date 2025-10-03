<?php
include 'db.php';
$res = $conn->query("SELECT id, title, regular_price, offer_price, thumbnail, pdf FROM ebooks ORDER BY id DESC");
$rows = [];
while($r = $res->fetch_assoc()) $rows[] = $r;
header('Content-Type: application/json');
echo json_encode($rows);
?>
