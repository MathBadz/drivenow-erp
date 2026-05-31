<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
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

Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});
