<?php

namespace App\Http\Resources;

use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Rental
 */
class RentalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'vehicle_id' => $this->vehicle_id,
            'vehicle_name' => $this->vehicle_name,
            'vehicle_plate' => $this->vehicle_plate,
            'pickup_branch' => $this->pickup_branch,
            'pickup_date' => $this->pickup_date?->toDateString(),
            'return_date' => $this->return_date?->toDateString(),
            'days' => $this->days,
            'daily_rate' => (float) $this->daily_rate,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'released_at' => $this->released_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
