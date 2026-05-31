<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(60, 1500);
        $penalty = fake()->boolean(25) ? fake()->numberBetween(20, 200) : 0;
        $discount = fake()->boolean(20) ? fake()->numberBetween(10, 100) : 0;
        $total = max(0, $subtotal + $penalty - $discount);
        $issued = Carbon::today()->subDays(fake()->numberBetween(0, 60));

        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'rental_reference' => 'RNT-'.strtoupper(fake()->bothify('????####')),
            'rental_id' => fake()->numberBetween(1, 40),
            'subtotal' => $subtotal,
            'penalty' => $penalty,
            'discount' => $discount,
            'total' => $total,
            'amount_paid' => 0,
            'balance' => $total,
            'status' => 'unpaid',
            'due_date' => (clone $issued)->addDays(14)->toDateString(),
            'issued_at' => $issued->toDateString(),
            'notes' => null,
        ];
    }
}
