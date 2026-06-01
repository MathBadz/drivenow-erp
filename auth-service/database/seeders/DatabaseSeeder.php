<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

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

        $this->seedActivity();
    }

    private function seedActivity(): void
    {
        $entries = [
            ['settings.updated', 'Global system settings were updated', 'warning', 'DriveNow Admin'],
            ['user.created', 'New staff account created: Front Desk Staff', 'success', 'DriveNow Admin'],
            ['auth.login', 'DriveNow Admin signed in', 'success', 'DriveNow Admin'],
            ['fleet.vehicle', 'Vehicle SUV-2024 marked Under Maintenance', 'info', 'Maintenance Lead'],
            ['rental.approved', 'Reservation RNT-8842 approved', 'success', 'Front Desk Staff'],
            ['billing.payment', 'Payment of $120.00 recorded on INV-2231', 'success', 'Front Desk Staff'],
            ['crm.blacklist', 'Customer flagged for repeated late returns', 'warning', 'Front Desk Staff'],
            ['maintenance.completed', 'Brake service completed on ABC-1234', 'success', 'Maintenance Lead'],
            ['auth.login', 'Front Desk Staff signed in', 'success', 'Front Desk Staff'],
            ['system.backup', 'Nightly database backup completed', 'info', 'System'],
        ];

        foreach ($entries as $i => [$event, $description, $level, $causer]) {
            ActivityLog::create([
                'event' => $event,
                'description' => $description,
                'level' => $level,
                'causer' => $causer,
                'created_at' => Carbon::now()->subHours($i * 5 + 1),
                'updated_at' => Carbon::now()->subHours($i * 5 + 1),
            ]);
        }
    }
}
