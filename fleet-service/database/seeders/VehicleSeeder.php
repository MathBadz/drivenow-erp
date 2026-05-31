<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        // A realistic mix — majority available, the rest spread across statuses.
        Vehicle::factory()->count(24)->available()->create();
        Vehicle::factory()->count(12)->create();
    }
}
