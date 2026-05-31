<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the global system settings with their documented defaults.
     */
    public function run(): void
    {
        Setting::setMany(Setting::DEFAULTS);
    }
}
