<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = $validated['from'] ?? Carbon::now()->subDays(29)->toDateString();
        $to = $validated['to'] ?? Carbon::now()->toDateString();

        $rows = $analytics->report($from, $to);

        return Inertia::render('Reports', [
            'filters' => ['from' => $from, 'to' => $to],
            'rows' => $rows,
            'totals' => [
                'rentals' => array_sum(array_column($rows, 'rentals')),
                'revenue' => array_sum(array_column($rows, 'revenue')),
                'returns' => array_sum(array_column($rows, 'returns')),
                'maintenance_cost' => array_sum(array_column($rows, 'maintenance_cost')),
            ],
        ]);
    }
}
