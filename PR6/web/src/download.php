<?php
// Простейшая защита: разрешаем только файлы из папки uploads и только pdf
$uploadDir = __DIR__ . '/uploads';

if (!isset($_GET['file'])) {
    http_response_code(400);
    echo "No file specified.";
    exit;
}

$file = $_GET['file'];

// Убираем возможные попытки ../ и т.п.
$baseName = basename($file);
$filePath = $uploadDir . '/' . $baseName;

if (!is_file($filePath)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// Заголовки для скачивания PDF
$originalName = preg_replace('/^\d+_/', '', $baseName);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . rawurlencode($originalName) . '"');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
