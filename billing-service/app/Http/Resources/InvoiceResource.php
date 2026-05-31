<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'rental_reference' => $this->rental_reference,
            'subtotal' => (float) $this->subtotal,
            'penalty' => (float) $this->penalty,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'amount_paid' => (float) $this->amount_paid,
            'balance' => (float) $this->balance,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'due_date' => $this->due_date?->toDateString(),
            'issued_at' => $this->issued_at?->toDateString(),
            'notes' => $this->notes,
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
