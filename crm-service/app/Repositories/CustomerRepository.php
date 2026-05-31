<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    /**
     * @param  array{search?: string|null, status?: string|null, tier?: string|null}  $filters
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Customer::query()
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['tier'] ?? null, fn ($q, $tier) => $q->where('tier', $tier))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $byStatus = Customer::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'active' => (int) $byStatus->get('active', 0),
            'inactive' => (int) $byStatus->get('inactive', 0),
            'blacklisted' => (int) $byStatus->get('blacklisted', 0),
        ];
    }
}
