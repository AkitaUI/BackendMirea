<?php
namespace App\Core;

class Controller
{
    protected function view(string $name, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../Views/layout.php';
    }
}
