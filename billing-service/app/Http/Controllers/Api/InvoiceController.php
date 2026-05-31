<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $invoices = Invoice::query()
            ->when($request->string('status')->toString() ?: null, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->get();

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice->load('payments'));
    }
}
