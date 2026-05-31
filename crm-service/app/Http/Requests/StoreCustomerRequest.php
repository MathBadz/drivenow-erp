<?php

namespace App\Http\Requests;

use App\Enums\CustomerTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'tier' => ['required', Rule::in(CustomerTier::values())],
            'loyalty_points' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
