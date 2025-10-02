<?php
// shapes.php
declare(strict_types=1);

/**
 * $params: ['shape','color','x','y','width','height']
 * Возвращает полный SVG документ как строку.
 * Масштабирует фигуру, чтобы она занимала примерно 30% экрана.
 */
function renderShapeSvg(array $params): string
{
    // Масштабируем координаты и размеры для более крупной фигуры
    $scale = 100; // коэффициент масштабирования для x/y
    $minSize = 300; // минимальный размер width/height

    $x = (int)($params['x'] ?? 0) * $scale;
    $y = (int)($params['y'] ?? 0) * $scale;
    $width = max((int)($params['width'] ?? 50), $minSize);
    $height = max((int)($params['height'] ?? 50), $minSize);

    // Увеличим width/height дополнительно для визуального эффекта
    $width = $width * 1.5;
    $height = $height * 1.5;

    $shape = $params['shape'] ?? 'rect';
    $color = $params['color'] ?? '#000000';

    // Паддинг вокруг фигуры
    $padding = 20;

    // Размер viewBox, чтобы фигура занимала примерно 30% видимой области
    $svgW = max($x + $width + $padding, 300);
    $svgH = max($y + $height + $padding, 300);

    $shapeSvg = '';

    switch ($shape) {
        case 'rect':
            $shapeSvg = sprintf(
                '<rect x="%d" y="%d" width="%d" height="%d" fill="%s" stroke="#000" />',
                $x, $y, $width, $height, htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE)
            );
            break;

        case 'circle':
            $r = (int)floor(min($width, $height) / 2);
            $cx = $x + $r;
            $cy = $y + $r;
            $shapeSvg = sprintf(
                '<circle cx="%d" cy="%d" r="%d" fill="%s" stroke="#000" />',
                $cx, $cy, $r, htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE)
            );
            break;

        case 'ellipse':
            $rx = $width / 2.0;
            $ry = $height / 2.0;
            $cx = $x + $rx;
            $cy = $y + $ry;
            $shapeSvg = sprintf(
                '<ellipse cx="%s" cy="%s" rx="%s" ry="%s" fill="%s" stroke="#000" />',
                $cx, $cy, $rx, $ry, htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE)
            );
            break;

        case 'line':
        default:
            $x2 = $x + $width;
            $y2 = $y + $height;
            $shapeSvg = sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="%s" stroke-width="4" />',
                $x, $y, $x2, $y2, htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE)
            );
            break;
    }

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg"
     width="{$svgW}" height="{$svgH}" viewBox="0 0 {$svgW} {$svgH}" role="img" aria-label="drawer">
  <rect width="100%" height="100%" fill="#ffffff" />
  {$shapeSvg}
</svg>
SVG;

    return $svg;
}
