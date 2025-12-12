<?php
// ВАЖНО: ни пробелов, ни пустых строк до <?php быть не должно.

// Простейший тест: рисуем картинку 400x200 с цветным прямоугольником и текстом.
$width  = 400;
$height = 200;

$image = imagecreatetruecolor($width, $height);

// Цвета
$white = imagecolorallocate($image, 255, 255, 255);
$blue  = imagecolorallocate($image, 50, 100, 220);
$black = imagecolorallocate($image, 0, 0, 0);
$gray  = imagecolorallocate($image, 200, 200, 200);

// Заливаем фон
imagefilledrectangle($image, 0, 0, $width, $height, $white);

// Рисуем прямоугольник
imagefilledrectangle($image, 50, 50, 350, 150, $blue);

// Рамка
imagerectangle($image, 50, 50, 350, 150, $black);

// Водяной знак / текст
$text = "TEST PNG";
imagestring($image, 5, 160, 90, $text, $black);

// Полупрозрачный водяной знак внизу
$wmColor = imagecolorallocatealpha($image, 0, 0, 0, 90);
imagestring($image, 3, 10, $height - 20, "Debug GD / PHP image", $wmColor);

// Отдаём как PNG
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
exit;
