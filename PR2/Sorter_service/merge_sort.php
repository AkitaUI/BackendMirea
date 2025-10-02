<?php
// merge_sort.php
declare(strict_types=1);

/**
 * Функция сортировки слиянием
 */
function mergeSort(array $arr): array
{
    $len = count($arr);
    if ($len <= 1) return $arr;

    $mid = intdiv($len, 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    $left = mergeSort($left);
    $right = mergeSort($right);

    return merge($left, $right);
}

/**
 * Функция слияния двух отсортированных массивов
 */
function merge(array $left, array $right): array
{
    $result = [];
    while (!empty($left) && !empty($right)) {
        if ($left[0] <= $right[0]) {
            $result[] = array_shift($left);
        } else {
            $result[] = array_shift($right);
        }
    }

    return array_merge($result, $left, $right);
}
