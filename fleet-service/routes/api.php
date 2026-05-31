<?php

use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

// Cross-service vehicle data (consumed by rental-service, analytics, etc.).
Route::get('vehicles', [VehicleController::class, 'index'])->name('api.vehicles.index');
Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('api.vehicles.show');
