<?php
require_once __DIR__ . '/../bootstrap.php';
?>

<?php
$dbh = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($dbh->connect_error) {
    die("DB connection error");
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $dbh->prepare('SELECT city, temp_c, summary, updated_at FROM cities WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($city, $temp_c, $summary, $updated_at);
if ($stmt->fetch()) {
    // ok
} else {
    header("HTTP/1.0 404 Not Found");
    echo "City not found";
    exit;
}
$stmt->close();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title><?=htmlspecialchars($city)?></title></head>
<body>
<h1>Погода: <?=htmlspecialchars($city)?></h1>
<p>Температура: <?=htmlspecialchars($temp_c)?> °C</p>
<p>Сводка: <?=htmlspecialchars($summary)?></p>
<p>Обновлено: <?=htmlspecialchars($updated_at)?></p>
<p><a href="/dynamic/index.php">Назад к списку</a></p>
</body></html>
