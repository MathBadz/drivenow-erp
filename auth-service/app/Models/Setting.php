<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    private const CACHE_KEY = 'settings.all';

    /**
     * Default values used until an administrator overrides them. Keeps the
     * platform branded out-of-the-box and guarantees every key resolves.
     *
     * @var array<string, string|null>
     */
    public const DEFAULTS = [
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
     * All stored settings as a flat key => value map (cached).
     *
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => self::query()->pluck('value', 'key')->all(),
        );
    }

    /**
     * Resolve a single setting, falling back to the documented default.
     */
    public static function value(string $key, ?string $default = null): ?string
    {
        return self::allValues()[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * Persist a batch of settings and flush the cache.
     *
     * @param  array<string, string|null>  $pairs
     */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'general'],
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The branding payload shared with every service and the public API.
     *
     * @return array<string, string|null>
     */
    public static function publicPayload(): array
    {
        $values = self::allValues();

        $payload = [];
        foreach (self::DEFAULTS as $key => $default) {
            $payload[$key] = $values[$key] ?? $default;
        }

        return $payload;
    }
}
