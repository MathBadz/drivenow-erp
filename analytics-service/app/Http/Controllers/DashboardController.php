<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(AnalyticsService $analytics): Response
    {
        return Inertia::render('Dashboard', $analytics->dashboard());
    }
}
