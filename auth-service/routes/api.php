<?php

use App\Http\Controllers\Api\SettingsController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected branding/configuration endpoint.
// auth-service is the single source of truth.
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('settings', SettingsController::class)->name('api.v1.settings');
});