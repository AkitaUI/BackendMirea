<?php
// drawer.php
// Вход: ?num=12345
// Вывод: SVG (Content-Type: image/svg+xml)

declare(strict_types=1);

require_once __DIR__ . '/decoder.php';
require_once __DIR__ . '/shapes.php';
require_once __DIR__ . '/utils.php';

$numParam = $_GET['num'] ?? null;

if ($numParam === null) {
    header('Content-Type: image/svg+xml');
    echo renderErrorSvg('Missing parameter: num');
    exit;
}

// Пытаемся распарсить целое (поддерживаем знаковые и беззнаковые значения)
if (!is_numeric($numParam)) {
    header('Content-Type: image/svg+xml');
    echo renderErrorSvg('Parameter num must be an integer');
    exit;
}

$num = (int)$numParam;

// Декодируем параметры фигуры
$params = decode_num($num);

// Генерируем SVG
$svg = renderShapeSvg($params);

// Выводим SVG
header('Content-Type: image/svg+xml; charset=utf-8');
echo $svg;
