<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
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

Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

// Customer record changes are limited to administrators and staff.
Route::middleware('role:admin,staff')->group(function () {
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::post('customers/{customer}/blacklist', [CustomerController::class, 'blacklist'])->name('customers.blacklist');
    Route::post('customers/{customer}/unblacklist', [CustomerController::class, 'unblacklist'])->name('customers.unblacklist');
    Route::post('customers/{customer}/feedback', [CustomerController::class, 'addFeedback'])->name('customers.feedback');
});
});
