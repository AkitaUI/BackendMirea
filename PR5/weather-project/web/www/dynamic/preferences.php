<?php
require_once __DIR__.'/../bootstrap.php';

// method POST: save preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'light'; // light/dark/contrast
    $lang = $_POST['lang'] ?? 'ru';

    // сохраняем в сессии
    $_SESSION['prefs'] = ['theme'=>$theme, 'lang'=>$lang];

    // также ставим cookie на 30 дней
    setcookie('theme', $theme, time()+60*60*24*30, '/');
    setcookie('lang', $lang, time()+60*60*24*30, '/');

    echo json_encode(['status'=>'ok','prefs'=>$_SESSION['prefs']]);
    exit;
}

// GET: вернуть текущие prefs (из сессии или cookie)
$prefs = $_SESSION['prefs'] ?? [
    'theme' => $_COOKIE['theme'] ?? 'light',
    'lang'  => $_COOKIE['lang'] ?? 'ru',
];

echo json_encode(['prefs' => $prefs, 'user' => $_SESSION['user'] ?? null]);
