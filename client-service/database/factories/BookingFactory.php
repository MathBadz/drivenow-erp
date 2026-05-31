<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * @var array<int, array{0: int, 1: string, 2: string, 3: string, 4: int}>
     */
    private const VEHICLES = [
        [1, 'Toyota Vios', 'sedan', 'ABC-1234', 60],
        [3, 'Toyota Fortuner', 'suv', 'SUV-2024', 120],
        [5, 'Toyota HiAce', 'van', 'VAN-0099', 140],
        [6, 'Ford Ranger', 'pickup', 'PKP-4521', 110],
        [7, 'Suzuki Swift', 'hatchback', 'HTB-3310', 45],
    ];

    public function definition(): array
    {
        [$id, $name, $category, $plate, $rate] = fake()->randomElement(self::VEHICLES);
        $pickup = Carbon::today()->addDays(fake()->numberBetween(-25, 20));
        $days = fake()->numberBetween(1, 7);
        $return = (clone $pickup)->addDays($days);

        return [
            'vehicle_id' => $id,
            'vehicle_name' => $name,
            'vehicle_category' => $category,
            'vehicle_plate' => $plate,
            'daily_rate' => $rate,
            'pickup_date' => $pickup->toDateString(),
            'return_date' => $return->toDateString(),
            'days' => $days,
            'total' => $rate * $days,
            'status' => fake()->randomElement([
                BookingStatus::Completed->value,
                BookingStatus::Completed->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Pending->value,
                BookingStatus::Cancelled->value,
            ]),
            'notes' => null,
        ];
    }
}
