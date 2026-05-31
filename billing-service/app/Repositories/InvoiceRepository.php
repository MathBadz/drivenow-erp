<?php

namespace App\Repositories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository
{
    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, Invoice>
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
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
        $byStatus = Invoice::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'unpaid' => (int) $byStatus->get('unpaid', 0),
            'partial' => (int) $byStatus->get('partial', 0),
            'paid' => (int) $byStatus->get('paid', 0),
            'overdue' => (int) $byStatus->get('overdue', 0),
            'refunded' => (int) $byStatus->get('refunded', 0),
            'invoiced' => (float) Invoice::query()->where('status', '!=', InvoiceStatus::Refunded->value)->sum('total'),
            'collected' => (float) Invoice::query()->sum('amount_paid'),
            'outstanding' => (float) Invoice::query()
                ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value, InvoiceStatus::Overdue->value])
                ->sum('balance'),
            'overdue_amount' => (float) Invoice::query()->where('status', InvoiceStatus::Overdue->value)->sum('balance'),
        ];
    }
}
