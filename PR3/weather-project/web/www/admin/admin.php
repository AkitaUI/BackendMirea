<?php
// admin.php - защита через HTTP Basic Auth (пользователи в БД)

// читаем авторизационный заголовок
$hasAuth = false;
if (!empty($_SERVER['PHP_AUTH_USER'])) {
    $user = $_SERVER['PHP_AUTH_USER'];
    $pass = $_SERVER['PHP_AUTH_PW'];
    $hasAuth = true;
} elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    // иногда PHP не заполняет PHP_AUTH_*, разберём заголовок
    if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        $creds = base64_decode($matches[1]);
        list($user, $pass) = explode(':', $creds, 2);
        $hasAuth = true;
    }
}

if (!$hasAuth) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authorization required';
    exit;
}

// соединение с базой (используем переменные окружения из docker-compose)
$dbh = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($dbh->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "DB connection error";
    exit;
}

// получим пароль для пользователя
$stmt = $dbh->prepare('SELECT password_hash FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $user);
$stmt->execute();
$stmt->bind_result($password_hash);
if (!$stmt->fetch()) {
    // пользователь не найден
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Invalid credentials';
    exit;
}
$stmt->close();

// сравнение пароля (используем password_hash()/password_verify())
if (!password_verify($pass, $password_hash)) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Invalid credentials';
    exit;
}

// аутентификация успешна — показываем админку
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin - Weather</title></head>
<body>
<h1>Admin area</h1>
<p>Здравствуйте, <?=htmlspecialchars($user)?>! Вы вошли в админку.</p>

<h2>Список городов (из БД)</h2>
<?php
$res = $dbh->query("SELECT id, city, temp_c, summary FROM cities ORDER BY city");
echo "<ul>";
while ($row = $res->fetch_assoc()) {
    echo "<li><a href=\"/dynamic/weather.php?id=".(int)$row['id']."\">"
       .htmlspecialchars($row['city'])."</a> — ".htmlspecialchars($row['temp_c'])."°C</li>";
}
echo "</ul>";
?>
</body>
</html>
