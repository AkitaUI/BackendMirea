<?php
namespace App\Services;

class ChartService
{
    public function render(int $type): void
    {
        require __DIR__ . '/../../chart.php';
    }
}
