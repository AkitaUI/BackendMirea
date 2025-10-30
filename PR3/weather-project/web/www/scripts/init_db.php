<?php
$db = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($db->connect_error) die($db->connect_error);

$db->query("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS cities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(100) NOT NULL,
  temp_c DECIMAL(4,1) NOT NULL,
  summary VARCHAR(255),
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// добавим тестового пользователя (admin / adminpass)
$pass_hash = password_hash('adminpass', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT IGNORE INTO users (username, password_hash) VALUES (?, ?)");
$user = 'admin';
$stmt->bind_param('ss', $user, $pass_hash);
$stmt->execute();

// добавим несколько городов
$db->query("INSERT IGNORE INTO cities (id, city, temp_c, summary) VALUES
  (1,'Moscow',5.0,'Облачно'),
  (2,'London',10.2,'Дождь'),
  (3,'New York',12.5,'Ясно')
");

// feedback
echo "OK\n";
