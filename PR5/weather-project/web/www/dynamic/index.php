<?php
require_once __DIR__ . '/../bootstrap.php';   // ← добавляем эту строку
$dbh = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
$res = $dbh->query("SELECT id, name, country, population FROM cities ORDER BY name");
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Список городов — Погода</title></head>
<body>
<h1>Города</h1>
<ul>
<?php while($r = $res->fetch_assoc()): ?>
  <li><a href="/dynamic/weather.php?id=<?=$r['id']?>"><?=htmlspecialchars($r['city'])?></a>
   — <?=$r['temp_c']?>°C — <?=htmlspecialchars($r['summary'])?></li>
<?php endwhile; ?>
</ul>
</body>
</html>
