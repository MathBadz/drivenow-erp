<?php

namespace App\Repositories;

use App\Enums\RentalStatus;
use App\Models\Rental;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RentalRepository
{
    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, Rental>
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Rental::query()
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, int|float>
     */
    public function stats(): array
    {
        $byStatus = Rental::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'pending' => (int) $byStatus->get('pending', 0),
            'approved' => (int) $byStatus->get('approved', 0),
            'active' => (int) $byStatus->get('active', 0),
            'completed' => (int) $byStatus->get('completed', 0),
            'cancelled' => (int) $byStatus->get('cancelled', 0),
            'revenue' => (float) Rental::query()
                ->whereIn('status', [RentalStatus::Active->value, RentalStatus::Completed->value])
                ->sum('total'),
        ];
    }

    /**
     * Detect a booking conflict: an overlapping rental for the same vehicle
     * that still occupies it (approved or active).
     */
    public function hasConflict(int $vehicleId, string $pickup, string $return, ?int $ignoreId = null): bool
    {
        return Rental::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', RentalStatus::blocking())
            ->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->where('pickup_date', '<=', $return)
            ->where('return_date', '>=', $pickup)
            ->exists();
    }
}
