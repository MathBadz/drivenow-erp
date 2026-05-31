<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
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

Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
Route::post('invoices/{invoice}/refund', [InvoiceController::class, 'refund'])->name('invoices.refund');
});
