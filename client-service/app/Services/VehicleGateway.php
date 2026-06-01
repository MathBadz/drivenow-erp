<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reads the public vehicle catalog from fleet-service. Falls back to a static
 * catalog when fleet-service is unreachable so the storefront always renders.
 */
class VehicleGateway
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const FALLBACK = [
        ['id' => 1, 'name' => '2023 Toyota Vios', 'make' => 'Toyota', 'model' => 'Vios', 'plate_number' => 'ABC-1234', 'category' => 'sedan', 'category_label' => 'Sedan', 'status' => 'available', 'daily_rate' => 60.0, 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Gasoline'],
        ['id' => 2, 'name' => '2022 Honda City', 'make' => 'Honda', 'model' => 'City', 'plate_number' => 'XYZ-5678', 'category' => 'sedan', 'category_label' => 'Sedan', 'status' => 'available', 'daily_rate' => 65.0, 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Gasoline'],
        ['id' => 3, 'name' => '2024 Toyota Fortuner', 'make' => 'Toyota', 'model' => 'Fortuner', 'plate_number' => 'SUV-2024', 'category' => 'suv', 'category_label' => 'SUV', 'status' => 'available', 'daily_rate' => 120.0, 'seats' => 7, 'transmission' => 'Automatic', 'fuel_type' => 'Diesel'],
        ['id' => 4, 'name' => '2023 Mitsubishi Montero', 'make' => 'Mitsubishi', 'model' => 'Montero', 'plate_number' => 'MON-7788', 'category' => 'suv', 'category_label' => 'SUV', 'status' => 'available', 'daily_rate' => 115.0, 'seats' => 7, 'transmission' => 'Automatic', 'fuel_type' => 'Diesel'],
        ['id' => 5, 'name' => '2023 Toyota HiAce', 'make' => 'Toyota', 'model' => 'HiAce', 'plate_number' => 'VAN-0099', 'category' => 'van', 'category_label' => 'Van', 'status' => 'available', 'daily_rate' => 140.0, 'seats' => 12, 'transmission' => 'Manual', 'fuel_type' => 'Diesel'],
        ['id' => 6, 'name' => '2022 Ford Ranger', 'make' => 'Ford', 'model' => 'Ranger', 'plate_number' => 'PKP-4521', 'category' => 'pickup', 'category_label' => 'Pickup Truck', 'status' => 'available', 'daily_rate' => 110.0, 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Diesel'],
        ['id' => 7, 'name' => '2023 Suzuki Swift', 'make' => 'Suzuki', 'model' => 'Swift', 'plate_number' => 'HTB-3310', 'category' => 'hatchback', 'category_label' => 'Hatchback', 'status' => 'available', 'daily_rate' => 45.0, 'seats' => 5, 'transmission' => 'Automatic', 'fuel_type' => 'Gasoline'],
        ['id' => 8, 'name' => '2024 Honda CR-V', 'make' => 'Honda', 'model' => 'CR-V', 'plate_number' => 'CRV-8821', 'category' => 'suv', 'category_label' => 'SUV', 'status' => 'available', 'daily_rate' => 100.0, 'seats' => 7, 'transmission' => 'Automatic', 'fuel_type' => 'Gasoline'],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return Cache::remember('storefront.vehicles', now()->addMinutes(2), function () {
            $base = config('services.fleet.url');

            if ($base) {
                try {
                    $response = Http::timeout(2)->acceptJson()
                        ->withHeaders(['X-Service-Token' => (string) config('services.service_token')])
                        ->get("{$base}/api/v1/vehicles");

                    if ($response->successful()) {
                        return collect($response->json('data') ?? [])->all();
                    }
                } catch (\Throwable) {
                    // Fleet offline.
                }
            }

            return self::FALLBACK;
        });
    }

    /**
     * Vehicles that can be booked right now.
     *
     * @return array<int, array<string, mixed>>
     */
    public function available(): array
    {
        return collect($this->all())
            ->whereIn('status', ['available', 'reserved'])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return collect($this->all())->firstWhere('id', $id);
    }
}
