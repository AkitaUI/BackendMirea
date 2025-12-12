<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\FixtureService;
use App\Services\ChartService;

class StatsController extends Controller
{
    private FixtureService $fixtures;
    private ChartService $charts;

    public function __construct()
    {
        $this->fixtures = new FixtureService();
        $this->charts = new ChartService();
    }

    public function index(): void
    {
        $this->view('stats', [
            'count' => $this->fixtures->count()
        ]);
    }

    public function chart(): void
    {
        $type = (int)($_GET['type'] ?? 1);
        $this->charts->render($type);
    }
}
