<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;

class SummaryController extends Controller
{
    /**
     * Headline analytics KPIs as JSON.
     */
    public function __invoke(AnalyticsService $analytics): JsonResponse
    {
        return response()->json(['data' => $analytics->dashboard()['kpis']]);
    }
}
