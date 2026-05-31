<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reads vehicles from fleet-service for the maintenance create form. Falls
 * back to a static catalog when fleet-service is unreachable.
 */
class VehicleGateway
{
    /**
     * @var array<int, array{id: int, name: string, plate_number: string}>
     */
    private const FALLBACK = [
        ['id' => 1, 'name' => '2023 Toyota Vios', 'plate_number' => 'ABC-1234'],
        ['id' => 2, 'name' => '2022 Honda City', 'plate_number' => 'XYZ-5678'],
        ['id' => 3, 'name' => '2024 Toyota Fortuner', 'plate_number' => 'SUV-2024'],
        ['id' => 4, 'name' => '2023 Mitsubishi Montero', 'plate_number' => 'MON-7788'],
        ['id' => 5, 'name' => '2023 Toyota HiAce', 'plate_number' => 'VAN-0099'],
        ['id' => 6, 'name' => '2022 Ford Ranger', 'plate_number' => 'PKP-4521'],
        ['id' => 7, 'name' => '2023 Suzuki Swift', 'plate_number' => 'HTB-3310'],
        ['id' => 8, 'name' => '2024 Honda CR-V', 'plate_number' => 'CRV-8821'],
    ];

    /**
     * @return array<int, array{id: int, name: string, plate_number: string}>
     */
    public function all(): array
    {
        return Cache::remember('fleet.vehicles', now()->addMinutes(2), function () {
            $base = config('services.fleet.url');

            if ($base) {
                try {
                    $response = Http::timeout(2)->acceptJson()->get("{$base}/api/vehicles");

                    if ($response->successful()) {
                        return collect($response->json('data') ?? [])
                            ->map(fn (array $v): array => [
                                'id' => (int) $v['id'],
                                'name' => $v['name'],
                                'plate_number' => $v['plate_number'],
                            ])
                            ->all();
                    }
                } catch (\Throwable) {
                    // Fleet offline.
                }
            }

            return self::FALLBACK;
        });
    }
}
