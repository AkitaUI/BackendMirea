<?php
require_once __DIR__.'/../bootstrap.php';
require_once __DIR__.'/../db_connect.php'; // $db - mysqli

// Проверка: только POST и авторизованный пользователь (optional)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}

// простой авторизационный пример: проверяем сессию
if (!isset($_SESSION['user'])) {
    http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
}

if (!isset($_FILES['pdf'])) {
    http_response_code(400); echo json_encode(['error'=>'No file uploaded']); exit;
}

$file = $_FILES['pdf'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); echo json_encode(['error'=>'Upload error']); exit;
}

// проверяем тип и расширение
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if ($mime !== 'application/pdf') {
    http_response_code(400); echo json_encode(['error'=>'Only PDFs allowed']); exit;
}

// формируем уникальное имя и сохраняем
$uploadsDir = __DIR__ . '/../../uploads';
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
$baseName = bin2hex(random_bytes(8)) . '_' . basename($file['name']);
$targetPath = $uploadsDir . '/' . $baseName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500); echo json_encode(['error'=>'Cannot move uploaded file']); exit;
}

// сохраняем метаданные в БД
$stmt = $db->prepare("INSERT INTO files (filename, filepath, uploaded_by) VALUES (?, ?, ?)");
$userid = $_SESSION['user']['id'];
$stmt->bind_param('ssi', $file['name'], $baseName, $userid);
$stmt->execute();

echo json_encode(['status'=>'ok','id'=> $stmt->insert_id, 'filename'=>$file['name']]);
