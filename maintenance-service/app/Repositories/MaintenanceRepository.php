<?php

namespace App\Repositories;

use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MaintenanceRepository
{
    /**
     * @param  array{search?: string|null, status?: string|null, type?: string|null}  $filters
     * @return LengthAwarePaginator<int, MaintenanceRecord>
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return MaintenanceRecord::query()
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, int|float>
     */
    public function stats(): array
    {
        $byStatus = MaintenanceRecord::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'scheduled' => (int) $byStatus->get('scheduled', 0),
            'in_progress' => (int) $byStatus->get('in_progress', 0),
            'completed' => (int) $byStatus->get('completed', 0),
            'cancelled' => (int) $byStatus->get('cancelled', 0),
            'total_cost' => (float) MaintenanceRecord::query()->sum('cost'),
            'downtime' => (int) MaintenanceRecord::query()
                ->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])
                ->count(),
        ];
    }
}
