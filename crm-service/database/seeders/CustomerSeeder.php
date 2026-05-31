<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()
            ->count(45)
            ->create()
            ->each(function (Customer $customer): void {
                $customer->log('account', 'Customer account created.');

                if ($customer->total_rentals > 0) {
                    $customer->log('rental', "Completed {$customer->total_rentals} rental(s) to date.");
                }

                for ($i = 0, $n = fake()->numberBetween(0, 3); $i < $n; $i++) {
                    $customer->feedback()->create([
                        'rating' => fake()->numberBetween(3, 5),
                        'comment' => fake()->sentence(),
                        'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
                    ]);
                }
            });

        Customer::factory()->count(5)->blacklisted()->create()->each(
            fn (Customer $c) => $c->log('blacklist', 'Customer blacklisted: '.$c->blacklist_reason)
        );
    }
}
