<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingsSeeder::class);

        // Primary administrator.
        User::factory()->admin()->create([
            'name' => 'DriveNow Admin',
            'email' => 'admin@drivenow.test',
        ]);

        // Operational staff.
        User::factory()->staff()->create([
            'name' => 'Front Desk Staff',
            'email' => 'staff@drivenow.test',
        ]);

        User::factory()->maintenance()->create([
            'name' => 'Maintenance Lead',
            'email' => 'maintenance@drivenow.test',
        ]);

        // Sample customers.
        User::factory()->count(8)->customer()->create();
    }
}
