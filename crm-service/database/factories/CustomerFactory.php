<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\CustomerTier;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        $points = fake()->numberBetween(0, 8000);
        $rentals = fake()->numberBetween(0, 40);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+63 9## ### ####'),
            'address' => fake()->streetAddress(),
            'city' => 'Bacolod City',
            'status' => fake()->randomElement([
                CustomerStatus::Active->value,
                CustomerStatus::Active->value,
                CustomerStatus::Active->value,
                CustomerStatus::Inactive->value,
            ]),
            'tier' => CustomerTier::fromPoints($points)->value,
            'loyalty_points' => $points,
            'total_rentals' => $rentals,
            'total_spent' => $rentals * fake()->numberBetween(60, 200),
            'joined_at' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'notes' => null,
        ];
    }

    public function blacklisted(): static
    {
        return $this->state(fn () => [
            'status' => CustomerStatus::Blacklisted->value,
            'blacklist_reason' => fake()->randomElement([
                'Repeated late returns',
                'Unpaid penalties',
                'Vehicle returned with major damage',
            ]),
        ]);
    }
}
