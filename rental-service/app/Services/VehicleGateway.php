<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reads available vehicles from fleet-service for the reservation form.
 * Falls back to a static catalog when fleet-service is unreachable so the
 * booking UI keeps working standalone.
 */
class VehicleGateway
{
    /**
     * @var array<int, array{id: int, name: string, plate_number: string, category_label: string, daily_rate: float}>
     */
    private const FALLBACK = [
        ['id' => 1, 'name' => '2023 Toyota Vios', 'plate_number' => 'ABC-1234', 'category_label' => 'Sedan', 'daily_rate' => 60.0],
        ['id' => 2, 'name' => '2022 Honda City', 'plate_number' => 'XYZ-5678', 'category_label' => 'Sedan', 'daily_rate' => 65.0],
        ['id' => 3, 'name' => '2024 Toyota Fortuner', 'plate_number' => 'SUV-2024', 'category_label' => 'SUV', 'daily_rate' => 120.0],
        ['id' => 4, 'name' => '2023 Mitsubishi Montero', 'plate_number' => 'MON-7788', 'category_label' => 'SUV', 'daily_rate' => 115.0],
        ['id' => 5, 'name' => '2023 Toyota HiAce', 'plate_number' => 'VAN-0099', 'category_label' => 'Van', 'daily_rate' => 140.0],
        ['id' => 6, 'name' => '2022 Ford Ranger', 'plate_number' => 'PKP-4521', 'category_label' => 'Pickup Truck', 'daily_rate' => 110.0],
        ['id' => 7, 'name' => '2023 Suzuki Swift', 'plate_number' => 'HTB-3310', 'category_label' => 'Hatchback', 'daily_rate' => 45.0],
        ['id' => 8, 'name' => '2024 Honda CR-V', 'plate_number' => 'CRV-8821', 'category_label' => 'SUV', 'daily_rate' => 100.0],
    ];

    /**
     * @return array<int, array{id: int, name: string, plate_number: string, category_label: string, daily_rate: float}>
     */
    public function available(): array
    {
        return Cache::remember('fleet.available_vehicles', now()->addMinutes(2), function () {
            $base = config('services.fleet.url');

            if ($base) {
                try {
                    $response = Http::timeout(2)->acceptJson()->withHeaders(['X-Service-Token' => (string) config('services.service_token')])->get("{$base}/api/v1/vehicles", ['status' => 'available']);

                    if ($response->successful()) {
                        return collect($response->json('data') ?? [])
                            ->map(fn (array $v): array => [
                                'id' => (int) $v['id'],
                                'name' => $v['name'],
                                'plate_number' => $v['plate_number'],
                                'category_label' => $v['category_label'],
                                'daily_rate' => (float) $v['daily_rate'],
                            ])
                            ->all();
                    }
                } catch (\Throwable) {
                    // Fleet offline — use fallback catalog.
                }
            }

            return self::FALLBACK;
        });
    }
}
