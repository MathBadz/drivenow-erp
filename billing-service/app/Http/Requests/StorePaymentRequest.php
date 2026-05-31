<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
            'method' => ['required', Rule::in(PaymentMethod::values())],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'reference' => ['nullable', 'string', 'max:60'],
        ];
    }
}
