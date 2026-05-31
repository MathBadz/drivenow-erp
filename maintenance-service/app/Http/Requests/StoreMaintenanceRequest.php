<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceSeverity;
use App\Enums\MaintenanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer'],
            'vehicle_name' => ['required', 'string', 'max:120'],
            'vehicle_plate' => ['required', 'string', 'max:20'],
            'type' => ['required', Rule::in(MaintenanceType::values())],
            'severity' => ['required', Rule::in(MaintenanceSeverity::values())],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'scheduled_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
