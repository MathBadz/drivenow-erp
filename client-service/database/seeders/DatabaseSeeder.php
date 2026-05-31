<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $customer = User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'customer@drivenow.test',
        ]);

        Booking::factory()->count(5)->for($customer)->create();
    }
}
