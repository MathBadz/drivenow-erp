<?php

namespace App\Http\Controllers;

use App\Enums\MaintenanceStatus;
use App\Http\Resources\MaintenanceResource;
use App\Models\MaintenanceRecord;
use App\Repositories\MaintenanceRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly MaintenanceRepository $records) {}

    public function __invoke(): Response
    {
        $byType = MaintenanceRecord::query()
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        return Inertia::render('Dashboard', [
            'stats' => $this->records->stats(),
            'byType' => [
                'inspection' => (int) $byType->get('inspection', 0),
                'repair' => (int) $byType->get('repair', 0),
                'scheduled' => (int) $byType->get('scheduled', 0),
                'damage' => (int) $byType->get('damage', 0),
            ],
            'upcoming' => MaintenanceResource::collection(
                MaintenanceRecord::query()
                    ->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])
                    ->orderBy('scheduled_date')
                    ->limit(6)
                    ->get()
            ),
        ]);
    }
}
