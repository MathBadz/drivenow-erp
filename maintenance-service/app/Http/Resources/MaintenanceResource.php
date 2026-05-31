<?php

namespace App\Http\Resources;

use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MaintenanceRecord
 */
class MaintenanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'vehicle_id' => $this->vehicle_id,
            'vehicle_name' => $this->vehicle_name,
            'vehicle_plate' => $this->vehicle_plate,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'severity' => $this->severity->value,
            'severity_label' => $this->severity->label(),
            'title' => $this->title,
            'description' => $this->description,
            'cost' => (float) $this->cost,
            'odometer' => $this->odometer,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'completed_date' => $this->completed_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
