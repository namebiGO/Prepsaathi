<?php
$file = $_GET['file'] ?? '';
$path = realpath(__DIR__ . '/' . $file);
$uploadsDir = realpath(__DIR__ . '/uploads') . DIRECTORY_SEPARATOR;

if(!$path || strpos($path, $uploadsDir) !== 0 || !file_exists($path)){
    http_response_code(404);
    echo 'Not found';
    exit;
}

$mime = mime_content_type($path);
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
readfile($path);
?>
