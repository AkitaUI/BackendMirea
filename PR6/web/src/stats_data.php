<?php
// web/src/stats_data.php

require __DIR__ . '/vendor/autoload.php';

use Faker\Factory as FakerFactory;

/**
 * Путь к файлу с фикстурами
 */
function fixtures_path(): string
{
    return __DIR__ . '/data/fixtures.json';
}

/**
 * Генерирует фикстуры, если они ещё не сгенерированы.
 * Возвращает массив фикстур.
 */
function get_fixtures(): array
{
    $path = fixtures_path();

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    if (!file_exists($path)) {
        $faker = FakerFactory::create('ru_RU');

        $fixtures = [];
        for ($i = 1; $i <= 50; $i++) {
            $fixtures[] = [
                'id'         => $i,
                'name'       => $faker->name(),
                'age'        => $faker->numberBetween(18, 65),
                'salary'     => $faker->numberBetween(30000, 150000),
                'department' => $faker->randomElement(['IT', 'HR', 'Sales', 'Marketing', 'Support']),
                'score'      => $faker->numberBetween(1, 100),
                'hired_at'   => $faker->date('Y-m-d'),
            ];
        }

        file_put_contents(
            $path,
            json_encode($fixtures, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

/**
 * Средняя зарплата по отделам (для столбчатого графика)
 */
function aggregate_salary_by_department(array $fixtures): array
{
    $sum  = [];
    $cnt  = [];

    foreach ($fixtures as $row) {
        $dept = $row['department'];
        $sal  = (int)$row['salary'];

        if (!isset($sum[$dept])) {
            $sum[$dept] = 0;
            $cnt[$dept] = 0;
        }
        $sum[$dept] += $sal;
        $cnt[$dept] += 1;
    }

    $result = [];
    foreach ($sum as $dept => $total) {
        $result[$dept] = round($total / $cnt[$dept]);
    }

    return $result;
}

/**
 * Распределение по возрасту (для линейного графика)
 */
function aggregate_count_by_age(array $fixtures): array
{
    $ages = [];

    foreach ($fixtures as $row) {
        $age = (int)$row['age'];
        if (!isset($ages[$age])) {
            $ages[$age] = 0;
        }
        $ages[$age]++;
    }

    ksort($ages);
    return $ages;
}

/**
 * Средний score по отделам (для третьего типа графика)
 */
function aggregate_score_by_department(array $fixtures): array
{
    $sum  = [];
    $cnt  = [];

    foreach ($fixtures as $row) {
        $dept  = $row['department'];
        $score = (int)$row['score'];

        if (!isset($sum[$dept])) {
            $sum[$dept] = 0;
            $cnt[$dept] = 0;
        }
        $sum[$dept] += $score;
        $cnt[$dept] += 1;
    }

    $result = [];
    foreach ($sum as $dept => $total) {
        $result[$dept] = round($total / $cnt[$dept], 1);
    }

    return $result;
}
