<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Public storefront.
Route::get('/', HomeController::class)->name('home');
Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])
    ->whereNumber('vehicle')
    ->name('vehicles.show');

// Customer account.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [BookingController::class, 'index'])->name('dashboard');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

require __DIR__.'/settings.php';
