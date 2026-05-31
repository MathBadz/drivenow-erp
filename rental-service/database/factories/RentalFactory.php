<?php

namespace Database\Factories;

use App\Enums\RentalStatus;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    /**
     * @var array<int, array{0: string, 1: string, 2: int}>
     */
    private const VEHICLES = [
        ['Toyota Vios', 'ABC-1234', 60],
        ['Honda City', 'XYZ-5678', 65],
        ['Toyota Fortuner', 'SUV-2024', 120],
        ['Mitsubishi Montero', 'MON-7788', 115],
        ['Toyota HiAce', 'VAN-0099', 140],
        ['Ford Ranger', 'PKP-4521', 110],
        ['Suzuki Swift', 'HTB-3310', 45],
        ['Honda CR-V', 'CRV-8821', 100],
    ];

    public function definition(): array
    {
        [$vehicleName, $plate, $rate] = fake()->randomElement(self::VEHICLES);

        $pickup = Carbon::today()->addDays(fake()->numberBetween(-20, 20));
        $days = fake()->numberBetween(1, 10);
        $return = (clone $pickup)->addDays($days);
        $subtotal = $rate * $days;
        $status = fake()->randomElement(RentalStatus::values());

        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('+63 9## ### ####'),
            'vehicle_id' => fake()->numberBetween(1, 36),
            'vehicle_name' => $vehicleName,
            'vehicle_plate' => $plate,
            'pickup_branch' => 'Main Branch',
            'pickup_date' => $pickup->toDateString(),
            'return_date' => $return->toDateString(),
            'days' => $days,
            'daily_rate' => $rate,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'status' => $status,
            'notes' => null,
            'approved_at' => in_array($status, ['approved', 'active', 'completed'], true) ? now() : null,
            'released_at' => in_array($status, ['active', 'completed'], true) ? now() : null,
            'returned_at' => $status === 'completed' ? now() : null,
            'cancelled_at' => $status === 'cancelled' ? now() : null,
        ];
    }
}
