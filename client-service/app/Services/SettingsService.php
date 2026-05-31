<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Consumes global branding/configuration from the auth-service (the single
 * source of truth). Results are cached; if the hub is unreachable we fall
 * back to sensible defaults so this service still renders standalone.
 */
class SettingsService
{
    private const CACHE_KEY = 'system.settings';

    /**
     * @var array<string, string|null>
     */
    private const DEFAULTS = [
        'business_name' => 'DriveNow Car Rental Services',
        'business_email' => 'hello@drivenow.test',
        'business_phone' => '+63 34 000 0000',
        'business_address' => 'Brgy. Alijis, Bacolod City, Negros Occidental, Philippines',
        'business_website' => 'https://drivenow.test',
        'business_description' => 'Reliable, affordable vehicle rentals for Bacolod City and nearby areas.',
        'logo_url' => null,
        'favicon_url' => null,
        'currency' => 'USD',
        'currency_symbol' => '$',
        'timezone' => 'Asia/Manila',
    ];

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function () {
            $base = config('services.auth.url');

            if (! $base) {
                return self::DEFAULTS;
            }

            try {
                $response = Http::timeout(2)->acceptJson()->get("{$base}/api/settings");

                if ($response->successful()) {
                    return array_merge(self::DEFAULTS, $response->json('data') ?? []);
                }
            } catch (\Throwable) {
                // Hub offline — fall through to defaults.
            }

            return self::DEFAULTS;
        });
    }
}
