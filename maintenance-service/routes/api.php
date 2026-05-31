<?php

use App\Http\Controllers\Api\MaintenanceController;
use Illuminate\Support\Facades\Route;

// Cross-service maintenance data (consumed by fleet, analytics, etc.).
Route::get('maintenance', [MaintenanceController::class, 'index'])->name('api.maintenance.index');
Route::get('maintenance/{record}', [MaintenanceController::class, 'show'])->name('api.maintenance.show');
