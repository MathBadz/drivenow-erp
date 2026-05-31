<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceRepository $invoices) {}

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'status' => $request->string('status')->toString() ?: null,
        ];

        return Inertia::render('invoices/Index', [
            'invoices' => InvoiceResource::collection($this->invoices->paginate($filters)),
            'stats' => $this->invoices->stats(),
            'filters' => $filters,
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load('payments');

        return Inertia::render('invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
            'methodOptions' => $this->methodOptions(),
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $total = max(0, (float) $data['subtotal'] + (float) $data['penalty'] - (float) $data['discount']);

        Invoice::create([
            ...$data,
            'total' => $total,
            'amount_paid' => 0,
            'balance' => $total,
            'status' => InvoiceStatus::Unpaid->value,
            'issued_at' => now()->toDateString(),
        ]);

        return to_route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return to_route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function recordPayment(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === InvoiceStatus::Refunded) {
            return back()->with('error', 'Cannot add a payment to a refunded invoice.');
        }

        DB::transaction(function () use ($request, $invoice): void {
            $invoice->payments()->create([
                ...$request->validated(),
                'paid_at' => now(),
            ]);
            $invoice->recalculate();
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function refund(Invoice $invoice): RedirectResponse
    {
        if ($invoice->amount_paid <= 0) {
            return back()->with('error', 'Nothing has been paid on this invoice.');
        }

        $invoice->update(['status' => InvoiceStatus::Refunded->value]);

        return back()->with('success', 'Invoice marked as refunded.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function methodOptions(): array
    {
        return array_map(
            fn (PaymentMethod $m) => ['value' => $m->value, 'label' => $m->label()],
            PaymentMethod::cases(),
        );
    }
}
