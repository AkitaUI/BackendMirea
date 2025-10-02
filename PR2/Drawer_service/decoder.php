<?php
// decoder.php

declare(strict_types=1);

/**
 * Декодирует 32-битное число в параметры фигуры.
 * Возвращает массив:
 *  [
 *    'shape' => 'rect'|'circle'|'ellipse'|'line',
 *    'color' => '#RRGGBB',
 *    'x' => int,
 *    'y' => int,
 *    'width' => int,
 *    'height' => int
 *  ]
 */
function decode_num(int $num): array
{
    // Работаем с 32-битной маской беззнаково
    $n = $num & 0xFFFFFFFF;

    // Биты 0-1 : shape (2 бита)
    $shapeBits = ($n >> 0) & 0x3;

    // Биты 2-5 : color (4 бита)
    $colorIdx = ($n >> 2) & 0xF;

    // Биты 6-13 : width (8 бит)
    $width = ($n >> 6) & 0xFF;

    // Биты 14-21 : height (8 бит)
    $height = ($n >> 14) & 0xFF;

    // Биты 22-26 : x (5 бит)
    $x = ($n >> 22) & 0x1F;

    // Биты 27-31 : y (5 бит)
    $y = ($n >> 27) & 0x1F;

    // Ограничения/дефолты: чтобы не получить нулевые размеры — зададим минимум 1
    if ($width <= 0) $width = 10;
    if ($height <= 0) $height = 10;

    // Преобразуем индекс формы и цвета
    $shape = shapeFromBits($shapeBits);
    $color = colorFromIndex($colorIdx);

    return [
        'shape' => $shape,
        'color' => $color,
        'x' => $x,
        'y' => $y,
        'width' => $width,
        'height' => $height,
    ];
}

function shapeFromBits(int $b): string
{
    switch ($b) {
        case 0: return 'rect';
        case 1: return 'circle';
        case 2: return 'ellipse';
        case 3:
        default:
            return 'line';
    }
}
