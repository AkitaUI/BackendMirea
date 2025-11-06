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
                $stmt = $pdo->prepare("SELECT id, name, country, population FROM cities WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); // ← ДОБАВЛЕН ФЛАГ
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "City not found"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); // ← ДОБАВЛЕН ФЛАГ
                }
            } else {
                $stmt = $pdo->query("SELECT id, name, country, population FROM cities ORDER BY name");
                echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); // ← ДОБАВЛЕН ФЛАГ
            }
            break;

        // ---- CREATE ----
        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data || !isset($data["name"]) || !isset($data["country"]) || !isset($data["population"])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields: name, country, population"]);
                exit;
            }
            
            // Проверяем, существует ли город с таким названием
            $check = $pdo->prepare("SELECT id FROM cities WHERE name = ? AND country = ?");
            $check->execute([$data["name"], $data["country"]]);
            if ($check->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "City already exists"]);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO cities (name, country, population) VALUES (?, ?, ?)");
            $stmt->execute([$data["name"], $data["country"], $data["population"]]);
            echo json_encode([
                "status" => "success",
                "id" => $pdo->lastInsertId(),
                "city" => [
                    "id" => $pdo->lastInsertId(),
                    "name" => $data["name"],
                    "country" => $data["country"],
                    "population" => $data["population"]
                ]
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
            if (!$data || !isset($data["name"]) || !isset($data["country"]) || !isset($data["population"])) {
                http_response_code(400);
                echo json_encode(["error" => "Missing required fields: name, country, population"]);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE cities SET name=?, country=?, population=? WHERE id=?");
            $updated = $stmt->execute([$data["name"], $data["country"], $data["population"], $id]);
            
            if ($updated && $stmt->rowCount() > 0) {
                echo json_encode(["status" => "updated", "id" => $id]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "City not found or no changes made"]);
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
            
            // Проверяем, есть ли связанные записи в weather
            $check = $pdo->prepare("SELECT id FROM weather WHERE city_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                http_response_code(409);
                echo json_encode(["error" => "Cannot delete city with weather records. Delete weather records first."]);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM cities WHERE id=?");
            $deleted = $stmt->execute([$id]);
            
            if ($deleted && $stmt->rowCount() > 0) {
                echo json_encode(["status" => "deleted", "id" => $id]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "City not found"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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