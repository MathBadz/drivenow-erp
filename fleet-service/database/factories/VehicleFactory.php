<?php

namespace Database\Factories;

use App\Enums\VehicleCategory;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Plausible make/model pairs grouped by category.
     *
     * @var array<string, array<int, array{0: string, 1: string}>>
     */
    private const CATALOG = [
        'sedan' => [['Toyota', 'Vios'], ['Honda', 'City'], ['Nissan', 'Almera'], ['Toyota', 'Corolla'], ['Mazda', '3']],
        'hatchback' => [['Suzuki', 'Swift'], ['Toyota', 'Wigo'], ['Honda', 'Brio'], ['Kia', 'Picanto']],
        'suv' => [['Toyota', 'Fortuner'], ['Mitsubishi', 'Montero'], ['Ford', 'Everest'], ['Honda', 'CR-V']],
        'van' => [['Toyota', 'HiAce'], ['Nissan', 'Urvan'], ['Maxus', 'V80']],
        'pickup' => [['Toyota', 'Hilux'], ['Ford', 'Ranger'], ['Mitsubishi', 'Strada'], ['Nissan', 'Navara']],
    ];

    public function definition(): array
    {
        $category = fake()->randomElement(VehicleCategory::values());
        [$make, $model] = fake()->randomElement(self::CATALOG[$category]);

        $rateByCategory = [
            'hatchback' => [35, 55],
            'sedan' => [45, 70],
            'suv' => [75, 130],
            'van' => [85, 150],
            'pickup' => [70, 120],
        ];
        [$min, $max] = $rateByCategory[$category];

        return [
            'make' => $make,
            'model' => $model,
            'year' => fake()->numberBetween(2018, 2025),
            'plate_number' => strtoupper(fake()->bothify('???-####')),
            'category' => $category,
            'status' => fake()->randomElement(VehicleStatus::values()),
            'branch' => 'Main Branch',
            'daily_rate' => fake()->numberBetween($min, $max),
            'seats' => $category === 'van' ? 12 : ($category === 'suv' ? 7 : 5),
            'transmission' => fake()->randomElement(['Automatic', 'Manual']),
            'fuel_type' => fake()->randomElement(['Gasoline', 'Diesel']),
            'color' => fake()->randomElement(['White', 'Silver', 'Black', 'Gray', 'Red', 'Blue']),
            'mileage' => fake()->numberBetween(5_000, 120_000),
            'image_url' => null,
            'notes' => null,
        ];
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => VehicleStatus::Available->value]);
    }
}
