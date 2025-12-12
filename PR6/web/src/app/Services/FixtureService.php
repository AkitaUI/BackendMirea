<?php
namespace App\Services;

require_once __DIR__ . '/../../stats_data.php';

class FixtureService
{
    public function getAll(): array
    {
        return get_fixtures();
    }

    public function count(): int
    {
        return count($this->getAll());
    }
}
