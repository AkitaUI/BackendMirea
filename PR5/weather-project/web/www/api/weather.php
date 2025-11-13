<?php
header('Content-Type: application/json; charset=utf-8');

// Получаем параметры из docker-compose.yml
$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASS') ?: 'apppass';
$name = getenv('DB_NAME') ?: 'weatherdb';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    switch ($method) {
        // ---- READ ----
        case 'GET':
            if (isset($_GET['id'])) {
                // Получаем данные о погоде + название города
                $stmt = $pdo->prepare("
                    SELECT w.*, c.name AS city_name, c.country 
                    FROM weather w
                    JOIN cities c ON w.city_id = c.id
                    WHERE w.id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $result = $stmt->fetch();
                
                if ($result) {
                    // Форматируем timestamp для удобства
                    $result['recorded_at'] = date('Y-m-d H:i:s', strtotime($result['recorded_at']));
                    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Weather record not found"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            } else {
                // Получаем все записи с названиями городов
                $stmt = $pdo->query("
                    SELECT w.*, c.name AS city_name, c.country 
                    FROM weather w
                    JOIN cities c ON w.city_id = c.id
                    ORDER BY w.recorded_at DESC
                ");
                $results = $stmt->fetchAll();
                
                // Форматируем timestamps
                foreach ($results as &$row) {
                    $row['recorded_at'] = date('Y-m-d H:i:s', strtotime($row['recorded_at']));
                }
                
                echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            break;

        // ---- CREATE ----
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data || !isset($data["city_id"]) || !isset($data["temperature"]) || !isset($data["condition_text"])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields: city_id, temperature, condition_text"]);
                exit;
            }
            
            // Проверяем существование города
            $cityCheck = $pdo->prepare("SELECT id FROM cities WHERE id = ?");
            $cityCheck->execute([$data["city_id"]]);
            if (!$cityCheck->fetch()) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid city_id: city does not exist"]);
                exit;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO weather (city_id, temperature, condition_text) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$data["city_id"], $data["temperature"], $data["condition_text"]]);
            
            // Получаем полные данные для ответа
            $newId = $pdo->lastInsertId();
            $response = $pdo->prepare("
                SELECT w.*, c.name AS city_name, c.country 
                FROM weather w
                JOIN cities c ON w.city_id = c.id
                WHERE w.id = ?
            ");
            $response->execute([$newId]);
            $result = $response->fetch();
            $result['recorded_at'] = date('Y-m-d H:i:s', strtotime($result['recorded_at']));
            
            echo json_encode([
                "status" => "success",
                "id" => $newId,
                "weather" => $result
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        // ---- UPDATE ----
        case 'PUT':
            parse_str($_SERVER["QUERY_STRING"], $query);
            $id = $query["id"] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Missing ID parameter"]);
                exit;
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data || !isset($data["city_id"]) || !isset($data["temperature"]) || !isset($data["condition_text"])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields: city_id, temperature, condition_text"]);
                exit;
            }
            
            // Проверяем существование города
            $cityCheck = $pdo->prepare("SELECT id FROM cities WHERE id = ?");
            $cityCheck->execute([$data["city_id"]]);
            if (!$cityCheck->fetch()) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid city_id: city does not exist"]);
                exit;
            }
            
            $stmt = $pdo->prepare("
                UPDATE weather 
                SET city_id = ?, temperature = ?, condition_text = ? 
                WHERE id = ?
            ");
            $updated = $stmt->execute([$data["city_id"], $data["temperature"], $data["condition_text"], $id]);
            
            if ($updated && $stmt->rowCount() > 0) {
                // Получаем обновленные данные
                $response = $pdo->prepare("
                    SELECT w.*, c.name AS city_name, c.country 
                    FROM weather w
                    JOIN cities c ON w.city_id = c.id
                    WHERE w.id = ?
                ");
                $response->execute([$id]);
                $result = $response->fetch();
                $result['recorded_at'] = date('Y-m-d H:i:s', strtotime($result['recorded_at']));
                
                echo json_encode([
                    "status" => "updated",
                    "id" => $id,
                    "weather" => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Weather record not found"]);
            }
            break;

        // ---- DELETE ----
        case 'DELETE':
            parse_str($_SERVER["QUERY_STRING"], $query);
            $id = $query["id"] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Missing ID parameter"]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM weather WHERE id = ?");
            $deleted = $stmt->execute([$id]);
            
            if ($deleted && $stmt->rowCount() > 0) {
                echo json_encode(["status" => "deleted", "id" => $id]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Weather record not found"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["error" => "Database connection failed"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    error_log("General error: " . $e->getMessage());
    echo json_encode(["error" => "Internal server error"]);
}