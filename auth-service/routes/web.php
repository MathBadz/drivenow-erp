<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\SystemController;
use Illuminate\Support\Facades\Route;

// The Operations Hub never shows a public landing page — go straight to login.
Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Global system settings — administrators only.
    Route::middleware('role:admin')->group(function () {
        Route::get('settings/system', [SystemController::class, 'edit'])->name('system.edit');
        Route::put('settings/system', [SystemController::class, 'update'])->name('system.update');
    });
});

require __DIR__.'/settings.php';
