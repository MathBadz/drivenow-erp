<?php

namespace App\Http\Requests;

use App\Enums\VehicleCategory;
use App\Enums\VehicleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only operational staff (admin / front-desk staff) may change fleet
        // inventory; maintenance and customer roles are read-only here.
        return in_array($this->user()?->role, ['admin', 'staff'], true);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'make' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'year' => ['required', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($vehicleId)],
            'category' => ['required', Rule::in(VehicleCategory::values())],
            'status' => ['required', Rule::in(VehicleStatus::values())],
            'branch' => ['required', 'string', 'max:120'],
            'daily_rate' => ['required', 'numeric', 'min:0', 'max:100000'],
            'seats' => ['required', 'integer', 'min:1', 'max:60'],
            'transmission' => ['required', 'string', 'max:30'],
            'fuel_type' => ['required', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:30'],
            'mileage' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
