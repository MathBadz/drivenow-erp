<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
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

Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

Route::post('customers/{customer}/blacklist', [CustomerController::class, 'blacklist'])->name('customers.blacklist');
Route::post('customers/{customer}/unblacklist', [CustomerController::class, 'unblacklist'])->name('customers.unblacklist');
Route::post('customers/{customer}/feedback', [CustomerController::class, 'addFeedback'])->name('customers.feedback');
});
