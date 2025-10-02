<?php
// sorter.php
declare(strict_types=1);

require_once __DIR__ . '/merge_sort.php';
require_once __DIR__ . '/utils.php';

$arrayParam = $_GET['array'] ?? null;

if ($arrayParam === null) {
    echo renderErrorHtml('Missing parameter: array');
    exit;
}

// Преобразуем строку "5,1,2,7,8" в массив чисел
$array = array_map('intval', array_map('trim', explode(',', $arrayParam)));

if (count($array) === 0) {
    echo renderErrorHtml('Array is empty or invalid');
    exit;
}

// Сортируем массив слиянием
$sortedArray = mergeSort($array);

// Генерируем HTML для результата
echo renderResultHtml($sortedArray);
