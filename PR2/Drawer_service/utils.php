<?php
// utils.php

declare(strict_types=1);

/**
 * Палитра из 16 цветов (индексы 0..15)
 */
function getColorPalette(): array
{
    return [
        '#000000', // 0 black
        '#FFFFFF', // 1 white
        '#FF0000', // 2 red
        '#00FF00', // 3 lime
        '#0000FF', // 4 blue
        '#FFFF00', // 5 yellow
        '#FF00FF', // 6 magenta
        '#00FFFF', // 7 cyan
        '#800000', // 8 maroon
        '#008000', // 9 green
        '#000080', // 10 navy
        '#808000', // 11 olive
        '#800080', // 12 purple
        '#008080', // 13 teal
        '#C0C0C0', // 14 silver
        '#808080', // 15 gray
    ];
}

/**
 * Возвращает hex-цвет по индексу (0..15). Если индекс вне диапазона — даст последний цвет.
 */
function colorFromIndex(int $idx): string
{
    $palette = getColorPalette();
    if ($idx < 0) $idx = 0;
    if ($idx >= count($palette)) $idx = count($palette) - 1;
    return $palette[$idx];
}

/**
 * Генерирует простое SVG-изображение ошибки (когда параметр отсутствует/неверный)
 */
function renderErrorSvg(string $message): string
{
    $w = 300; $h = 80;
    $escaped = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$w" height="$h" viewBox="0 0 $w $h" role="img" aria-label="error">
  <rect width="100%" height="100%" fill="#fff" stroke="#f00" />
  <text x="10" y="35" font-family="Arial, Helvetica, sans-serif" font-size="14" fill="#000">Error: {$escaped}</text>
  <text x="10" y="55" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#666">Provide ?num=INT</text>
</svg>
SVG;
}
