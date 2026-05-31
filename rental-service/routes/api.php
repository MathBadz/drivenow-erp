<?php

use App\Http\Controllers\Api\RentalController;
use Illuminate\Support\Facades\Route;

// Cross-service rental data (consumed by billing-service, analytics, etc.).
Route::get('rentals', [RentalController::class, 'index'])->name('api.rentals.index');
Route::get('rentals/{rental}', [RentalController::class, 'show'])->name('api.rentals.show');
