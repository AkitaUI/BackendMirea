<?php
// web/src/stats.php

require __DIR__ . '/stats_data.php';

$fixtures = get_fixtures();
$count = count($fixtures);

// Для краткого описания:
$sample = $fixtures[0] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Статистика (ПР6)</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .stats-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem 3rem;
        }
        .chart-block {
            margin-top: 2rem;
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .chart-block h2 {
            margin-top: 0;
        }
        .chart-block img {
            max-width: 100%;
            display: block;
            margin: 0 auto;
        }
        body.theme-dark .chart-block {
            background: #1f1f1f;
        }
    </style>
</head>
<body class="theme-light">
<div class="stats-container">
    <h1>Страница статистики (Практическая работа №6)</h1>

    <section class="card">
        <h2>Описание фикстур</h2>
        <p>Всего записей: <strong><?= (int)$count ?></strong></p>

        <?php if ($sample): ?>
            <p>Каждая фикстура содержит поля, например:</p>
            <ul>
                <li><code>id</code> — идентификатор</li>
                <li><code>name</code> — имя сотрудника (например: <em><?= htmlspecialchars($sample['name']) ?></em>)</li>
                <li><code>age</code> — возраст</li>
                <li><code>salary</code> — зарплата</li>
                <li><code>department</code> — отдел (IT, HR, Sales, Marketing, Support)</li>
                <li><code>score</code> — условный рейтинг сотрудника</li>
                <li><code>hired_at</code> — дата приёма на работу</li>
            </ul>
        <?php else: ?>
            <p>Фикстуры не найдены — возможно, произошла ошибка при генерации.</p>
        <?php endif; ?>
    </section>

    <section class="chart-block">
        <h2>График 1: Средняя зарплата по отделам</h2>
        <img src="chart.php?type=1" alt="Средняя зарплата по отделам">
    </section>

    <section class="chart-block">
        <h2>График 2: Распределение сотрудников по возрасту</h2>
        <img src="chart.php?type=2" alt="Распределение сотрудников по возрасту">
    </section>

    <section class="chart-block">
        <h2>График 3: Средний рейтинг (score) по отделам</h2>
        <img src="chart.php?type=3" alt="Средний рейтинг по отделам">
    </section>

    <p style="margin-top:2rem;">
        <a href="index.php">← Назад на главную</a>
    </p>
</div>
</body>
</html>
