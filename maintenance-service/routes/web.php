<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
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

Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
Route::get('maintenance/{record}', [MaintenanceController::class, 'show'])->name('maintenance.show');
Route::put('maintenance/{record}', [MaintenanceController::class, 'update'])->name('maintenance.update');
Route::delete('maintenance/{record}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

Route::post('maintenance/{record}/start', [MaintenanceController::class, 'start'])->name('maintenance.start');
Route::post('maintenance/{record}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
Route::post('maintenance/{record}/cancel', [MaintenanceController::class, 'cancel'])->name('maintenance.cancel');
});
