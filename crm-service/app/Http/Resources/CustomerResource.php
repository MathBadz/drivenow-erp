<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'tier' => $this->tier->value,
            'tier_label' => $this->tier->label(),
            'loyalty_points' => $this->loyalty_points,
            'total_rentals' => $this->total_rentals,
            'total_spent' => (float) $this->total_spent,
            'blacklist_reason' => $this->blacklist_reason,
            'joined_at' => $this->joined_at?->toDateString(),
            'notes' => $this->notes,
            'activities' => CustomerActivityResource::collection($this->whenLoaded('activities')),
            'feedback' => CustomerFeedbackResource::collection($this->whenLoaded('feedback')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
