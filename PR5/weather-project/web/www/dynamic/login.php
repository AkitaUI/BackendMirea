<?php
require_once __DIR__.'/../bootstrap.php';
require_once __DIR__.'/../db_connect.php'; // файл с mysqli подключением (если есть)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // проверяем пользователя в таблице users
    $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user'] = ['id' => $id, 'username' => $username];
        // optional: set cookie for remembering user name
        setcookie('preferred_user', $username, time()+60*60*24*30, '/', '', false, true);
        echo json_encode(['status'=>'ok','message'=>'Logged in']);
    } else {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'Invalid credentials']);
    }
    exit;
}
?>
<!-- simple HTML form if opened in browser -->
<form method="post">
  <input name="username" placeholder="Username"/>
  <input name="password" type="password" placeholder="Password"/>
  <button type="submit">Login</button>
</form>
