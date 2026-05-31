<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\InvoiceRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly InvoiceRepository $invoices) {}

    public function __invoke(): Response
    {
        $byMethod = Payment::query()
            ->selectRaw('method, sum(amount) as aggregate')
            ->groupBy('method')
            ->pluck('aggregate', 'method');

        return Inertia::render('Dashboard', [
            'stats' => $this->invoices->stats(),
            'byMethod' => [
                'cash' => (float) $byMethod->get('cash', 0),
                'gcash' => (float) $byMethod->get('gcash', 0),
                'card' => (float) $byMethod->get('card', 0),
            ],
            'overdueInvoices' => InvoiceResource::collection(
                Invoice::query()->where('status', InvoiceStatus::Overdue->value)->orderBy('due_date')->limit(5)->get()
            ),
            'recent' => InvoiceResource::collection(
                Invoice::query()->latest('id')->limit(6)->get()
            ),
        ]);
    }
}
