<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware('auth')->group(function () {

    Route::post('logout', function (IlluminateHttpRequest $request) {
        IlluminateSupportacadesauth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('http://localhost:8001/login');
    })->name('logout');

Route::get('dashboard', DashboardController::class)->name('dashboard');

Route::get('reservations', [RentalController::class, 'index'])->name('reservations.index');
Route::post('reservations', [RentalController::class, 'store'])->name('reservations.store');
Route::get('reservations/{rental}', [RentalController::class, 'show'])->name('reservations.show');
Route::delete('reservations/{rental}', [RentalController::class, 'destroy'])->name('reservations.destroy');

// Workflow transitions.
Route::post('reservations/{rental}/approve', [RentalController::class, 'approve'])->name('reservations.approve');
Route::post('reservations/{rental}/release', [RentalController::class, 'release'])->name('reservations.release');
Route::post('reservations/{rental}/return', [RentalController::class, 'return'])->name('reservations.return');
Route::post('reservations/{rental}/cancel', [RentalController::class, 'cancel'])->name('reservations.cancel');
Route::post('reservations/{rental}/extend', [RentalController::class, 'extend'])->name('reservations.extend');
});
