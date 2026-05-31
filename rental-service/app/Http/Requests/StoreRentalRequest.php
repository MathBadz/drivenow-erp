<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'vehicle_id' => ['required', 'integer'],
            'vehicle_name' => ['required', 'string', 'max:120'],
            'vehicle_plate' => ['required', 'string', 'max:20'],
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'pickup_branch' => ['required', 'string', 'max:120'],
            'pickup_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
