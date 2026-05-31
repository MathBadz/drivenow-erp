<?php

namespace App\Http\Resources;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vehicle
 */
class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name(),
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'plate_number' => $this->plate_number,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'branch' => $this->branch,
            'daily_rate' => (float) $this->daily_rate,
            'seats' => $this->seats,
            'transmission' => $this->transmission,
            'fuel_type' => $this->fuel_type,
            'color' => $this->color,
            'mileage' => $this->mileage,
            'image_url' => $this->image_url,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
