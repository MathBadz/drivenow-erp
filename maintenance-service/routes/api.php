<?php

use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected maintenance data (consumed by fleet, analytics, etc.).
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('api.v1.maintenance.index');
    Route::get('maintenance/{record}', [MaintenanceController::class, 'show'])->name('api.v1.maintenance.show');
});