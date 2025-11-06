<?php
// Получаем параметры из docker-compose.yml с fallback-значениями
$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASS') ?: 'apppass';
$name = getenv('DB_NAME') ?: 'weatherdb';

// Включаем детальные ошибки и исключения
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $user, $pass, $name);
$db->set_charset("utf8mb4");

echo "✅ Подключено к базе данных '$name' на хосте '$host' как пользователь '$user'\n";

// Безопасное удаление таблиц (сначала дочерние, потом родительские)
$db->query("SET FOREIGN_KEY_CHECKS = 0");
$tables = ['weather', 'cities', 'users'];
foreach ($tables as $table) {
    $db->query("DROP TABLE IF EXISTS `$table`");
    echo "🗑️  Таблица '$table' удалена\n";
}
$db->query("SET FOREIGN_KEY_CHECKS = 1");

// ---- Создание таблиц ----
$db->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "🆕 Таблица 'users' создана\n";

$db->query("CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100),
    population INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "🆕 Таблица 'cities' создана\n";

$db->query("CREATE TABLE weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    temperature DECIMAL(5,2),
    condition_text VARCHAR(100),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "🆕 Таблица 'weather' создана\n";

// ---- Вставка тестовых данных ----
// Пользователь
$pass_hash = password_hash('adminpass', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
$username = 'admin';
$stmt->bind_param('ss', $username, $pass_hash);
$stmt->execute();
echo "👤 Пользователь 'admin' создан (пароль: adminpass)\n";

// Города
$db->query("INSERT INTO cities (id, name, country, population) VALUES
    (1, 'Москва', 'Россия', 12655050),
    (2, 'Санкт-Петербург', 'Россия', 5383890),
    (3, 'Казань', 'Россия', 1301181)
");
echo "🏙️  Добавлены 3 города\n";

// Погода
$db->query("INSERT INTO weather (city_id, temperature, condition_text) VALUES
    (1, 5.5, 'Облачно'),
    (2, 8.2, 'Дождь'),
    (3, 10.1, 'Ясно')
");
echo "🌤️  Добавлены данные о погоде для 3 городов\n";

echo "\n✅ Инициализация базы данных завершена!\n";
echo "🔍 Для проверки выполните:\n";
echo "   curl http://localhost/api/cities.php\n";
echo "   curl http://localhost/api/weather.php\n";
?>