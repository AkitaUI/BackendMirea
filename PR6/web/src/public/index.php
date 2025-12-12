<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\StatsController;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/stats', [StatsController::class, 'index']);
$router->get('/chart', [StatsController::class, 'chart']);

$router->dispatch();
