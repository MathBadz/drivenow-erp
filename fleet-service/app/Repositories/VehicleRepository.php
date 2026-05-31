<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VehicleRepository
{
    /**
     * Paginated, filtered vehicle listing.
     *
     * @param  array{search?: string|null, status?: string|null, category?: string|null}  $filters
     * @return LengthAwarePaginator<int, Vehicle>
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Vehicle::query()
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Status counts for the fleet (single grouped query).
     *
     * @return array<string, int>
     */
    public function stats(): array
    {
        $byStatus = Vehicle::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'available' => (int) $byStatus->get('available', 0),
            'reserved' => (int) $byStatus->get('reserved', 0),
            'rented' => (int) $byStatus->get('rented', 0),
            'maintenance' => (int) $byStatus->get('maintenance', 0),
            'inactive' => (int) $byStatus->get('inactive', 0),
        ];
    }
}
