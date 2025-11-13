<?php
require_once __DIR__.'/../bootstrap.php';
require_once __DIR__.'/../db_connect.php';

$res = $db->query("SELECT id, filename, uploaded_at, uploaded_by FROM files ORDER BY uploaded_at DESC");
$files = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode($files);
