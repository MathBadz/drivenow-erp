<?php

use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

// Public branding/configuration endpoint consumed by the other microservices.
// auth-service is the single source of truth (see SPECIALIZED_INSTRUCTIONS).
Route::get('settings', SettingsController::class)->name('api.settings');
