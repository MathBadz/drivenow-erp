<?php

use App\Http\Controllers\Api\RentalController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected rental data (consumed by billing, crm, analytics, etc.).
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('rentals', [RentalController::class, 'index'])->name('api.v1.rentals.index');
    Route::get('rentals/{rental}', [RentalController::class, 'show'])->name('api.v1.rentals.show');
});