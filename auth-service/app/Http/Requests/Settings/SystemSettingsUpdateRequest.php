<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_website' => ['nullable', 'url', 'max:255'],
            'business_description' => ['nullable', 'string', 'max:1000'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'timezone' => ['required', 'string', 'max:64'],
        ];
    }
}
