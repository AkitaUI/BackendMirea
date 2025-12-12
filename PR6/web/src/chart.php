<?php

require __DIR__ . '/stats_data.php';

$fixtures = get_fixtures();
$type = $_GET['type'] ?? '1';


switch ($type) {
    case '1':
        $data = aggregate_salary_by_department($fixtures);
        $title = 'Средняя зарплата по отделам';
        break;

    case '2':
        $data = aggregate_count_by_age($fixtures);
        $title = 'Распределение сотрудников по возрасту';
        break;

    default:
    case '3':
        $data = aggregate_score_by_department($fixtures);
        $title = 'Средний рейтинг (score) по отделам';
        break;
}

if (!$data) {
    $data = ['нет данных' => 1];
}


$width  = 900;
$height = 450;

$image = imagecreatetruecolor($width, $height);

$white = imagecolorallocate($image, 255, 255, 255);
$axis  = imagecolorallocate($image, 0, 0, 0);
$bar   = imagecolorallocate($image, 50, 100, 220);
$text  = imagecolorallocate($image, 0, 0, 0);
$grid  = imagecolorallocate($image, 220, 220, 220);

// Фон
imagefilledrectangle($image, 0, 0, $width, $height, $white);

// Заголовок
imagestring($image, 5, 10, 10, $title, $text);

// Подготовка
$values = array_values($data);
$labels = array_keys($data);

$max = max($values);
if ($max <= 0) {
    $max = 1;
}

// Область графика
$originX = 70;
$originY = $height - 60;
$graphWidth  = $width - $originX - 40;
$graphHeight = $originY - 50;

// Сетка по Y (5 делений)
$steps = 5;
for ($i = 0; $i <= $steps; $i++) {
    $y = $originY - ($graphHeight / $steps) * $i;
    imageline($image, $originX, $y, $originX + $graphWidth, $y, $grid);

    $valLabel = round($max / $steps * $i);
    imagestring($image, 2, 5, $y - 7, $valLabel, $text);
}

// Оси
imageline($image, $originX, 50, $originX, $originY, $axis);                    // Y
imageline($image, $originX, $originY, $originX + $graphWidth, $originY, $axis); // X

/*
 * Типы графиков:
 *  type=1 — столбцы
 *  type=2 — "линеечка" (имитация линейного графика)
 *  type=3 — столбцы с точками (визуально иной тип)
 */
$count = count($values);
$chunkWidth = $graphWidth / max($count, 1);
$barWidth   = max(20, $chunkWidth * 0.5);

$prevX = null;
$prevY = null;

for ($i = 0; $i < $count; $i++) {
    $val = $values[$i];
    $ratio = $val / $max;

    $centerX = (int)($originX + $chunkWidth * $i + $chunkWidth / 2);
    $yVal    = (int)($originY - $graphHeight * $ratio);

    if ($type === '1' || $type === '3') {
        // Столбец
        $x1 = $centerX - (int)($barWidth / 2);
        $x2 = $centerX + (int)($barWidth / 2);
        imagefilledrectangle($image, $x1, $yVal, $x2, $originY, $bar);
        imagerectangle($image, $x1, $yVal, $x2, $originY, $axis);
    }

    if ($type === '2' || $type === '3') {
        // Линия/точки
        imagefilledellipse($image, $centerX, $yVal, 6, 6, $axis);

        if ($prevX !== null && $prevY !== null && $type === '2') {
            imageline($image, $prevX, $prevY, $centerX, $yVal, $axis);
        }

        $prevX = $centerX;
        $prevY = $yVal;
    }

    // подпись по X
    $label = (string)$labels[$i];
    if (mb_strlen($label) > 8) {
        $label = mb_substr($label, 0, 8) . '…';
    }
    imagestringup($image, 2, $centerX - 6, $originY + 25, $label, $text);

    // значение над столбцом/точкой
    imagestring($image, 2, $centerX - 10, $yVal - 15, $val, $text);
}

// Водяной знак
$wm = "Иван Иванов • ПР6";
$wmColor = imagecolorallocatealpha($image, 0, 0, 0, 90);
imagestring($image, 3, $width - 200, $height - 25, $wm, $wmColor);

// Отдаём PNG
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
exit;
