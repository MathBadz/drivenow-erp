<?php

use App\Http\Controllers\Api\VehicleController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected vehicle data (consumed by rental, analytics, etc.).
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('vehicles', [VehicleController::class, 'index'])->name('api.v1.vehicles.index');
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('api.v1.vehicles.show');
});