<?php

namespace Database\Factories;

use App\Enums\MaintenanceSeverity;
use App\Enums\MaintenanceStatus;
use App\Enums\MaintenanceType;
use App\Models\MaintenanceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<MaintenanceRecord>
 */
class MaintenanceRecordFactory extends Factory
{
    /**
     * @var array<int, array{0: string, 1: string}>
     */
    private const VEHICLES = [
        ['Toyota Vios', 'ABC-1234'], ['Honda City', 'XYZ-5678'], ['Toyota Fortuner', 'SUV-2024'],
        ['Mitsubishi Montero', 'MON-7788'], ['Toyota HiAce', 'VAN-0099'], ['Ford Ranger', 'PKP-4521'],
        ['Suzuki Swift', 'HTB-3310'], ['Honda CR-V', 'CRV-8821'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const TITLES = [
        'inspection' => ['Routine safety inspection', 'Pre-rental inspection', 'Post-return inspection'],
        'repair' => ['Brake pad replacement', 'AC compressor repair', 'Transmission service'],
        'scheduled' => ['5,000 km service', '10,000 km service', 'Oil & filter change'],
        'damage' => ['Front bumper dent repair', 'Windshield crack', 'Side mirror replacement'],
    ];

    public function definition(): array
    {
        [$vehicleName, $plate] = fake()->randomElement(self::VEHICLES);
        $type = fake()->randomElement(MaintenanceType::values());
        $status = fake()->randomElement(MaintenanceStatus::values());
        $scheduled = Carbon::today()->addDays(fake()->numberBetween(-30, 21));
        $completed = $status === 'completed' ? (clone $scheduled)->addDays(fake()->numberBetween(0, 3)) : null;

        return [
            'vehicle_id' => fake()->numberBetween(1, 36),
            'vehicle_name' => $vehicleName,
            'vehicle_plate' => $plate,
            'type' => $type,
            'status' => $status,
            'severity' => fake()->randomElement(MaintenanceSeverity::values()),
            'title' => fake()->randomElement(self::TITLES[$type]),
            'description' => fake()->sentence(10),
            'cost' => $type === 'inspection' ? fake()->numberBetween(0, 50) : fake()->numberBetween(50, 800),
            'odometer' => fake()->numberBetween(5_000, 120_000),
            'scheduled_date' => $scheduled->toDateString(),
            'completed_date' => $completed?->toDateString(),
            'notes' => null,
        ];
    }
}
