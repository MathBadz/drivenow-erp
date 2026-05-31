<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'rental_reference' => ['nullable', 'string', 'max:40'],
            'subtotal' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'penalty' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'discount' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
