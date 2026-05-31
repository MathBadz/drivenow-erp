<?php

namespace Database\Seeders;

use App\Models\MaintenanceRecord;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        MaintenanceRecord::factory()->count(40)->create();
    }
}
