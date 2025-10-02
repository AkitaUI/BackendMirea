<?php
// utils.php
declare(strict_types=1);

/**
 * Генерация HTML-страницы с результатом
 */
function renderResultHtml(array $sortedArray): string
{
    $htmlArray = implode(', ', $sortedArray);
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sorted Array</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h1 { color: #333; }
p { font-size: 18px; }
</style>
</head>
<body>
<h1>Sorted Array</h1>
<p>{$htmlArray}</p>
</body>
</html>
HTML;
}

/**
 * Генерация HTML ошибки
 */
function renderErrorHtml(string $message): string
{
    $escaped = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE);
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Error</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #fee; }
h1 { color: #900; }
</style>
</head>
<body>
<h1>Error</h1>
<p>{$escaped}</p>
</body>
</html>
HTML;
}
