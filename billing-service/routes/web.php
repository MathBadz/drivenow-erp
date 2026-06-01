<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');
// Cross-domain SSO receiver. Each service may run on its own host (on Render every
// service has a different *.onrender.com domain), so the hub hands the signed token
// here and this service sets its OWN drivenow_token cookie, then continues.
Route::get('sso', function (\Illuminate\Http\Request $request) {
    $token = (string) $request->query('t', '');
    $next = (string) $request->query('next', '/dashboard');
    if (! str_starts_with($next, '/') || str_starts_with($next, '//')) {
        $next = '/dashboard';
    }
    if (app(\App\Services\JwtService::class)->verify($token)) {
        $minutes = (int) config('session.lifetime', 120);
        \Illuminate\Support\Facades\Cookie::queue(
            \Illuminate\Support\Facades\Cookie::make('drivenow_token', $token, $minutes, '/', null, $request->secure(), true, false, 'lax')
        );

        return redirect($next);
    }

    return redirect(rtrim((string) config('services.auth.url', 'http://localhost:8001'), '/').'/login');
})->name('sso');

Route::middleware('auth')->group(function () {

    Route::post('logout', function (\Illuminate\Http\Request $request) {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(rtrim((string) config('services.auth.url', 'http://localhost:8001'), '/').'/login');
    })->name('logout');

Route::get('dashboard', DashboardController::class)->name('dashboard');

Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

// Invoicing, payments and refunds are limited to administrators and staff.
Route::middleware('role:admin,staff')->group(function () {
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
    Route::post('invoices/{invoice}/refund', [InvoiceController::class, 'refund'])->name('invoices.refund');
});
});
