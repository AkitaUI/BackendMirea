<?php
// download.php?id=123
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
require_once __DIR__.'/../db_connect.php';
$stmt = $db->prepare("SELECT filename, filepath FROM files WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($filename, $filepath);
if (!$stmt->fetch()) {
    http_response_code(404); echo "Not found"; exit;
}
$stmt->close();

$fullpath = __DIR__ . '/../../uploads/' . $filepath;
if (!file_exists($fullpath)) {
    http_response_code(404); echo "File not found"; exit;
}
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
readfile($fullpath);
exit;
