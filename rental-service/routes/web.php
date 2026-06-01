<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RentalController;
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

Route::get('reservations', [RentalController::class, 'index'])->name('reservations.index');
Route::get('reservations/{rental}', [RentalController::class, 'show'])->name('reservations.show');

// Bookings and workflow transitions are limited to administrators and staff.
Route::middleware('role:admin,staff')->group(function () {
    Route::post('reservations', [RentalController::class, 'store'])->name('reservations.store');
    Route::delete('reservations/{rental}', [RentalController::class, 'destroy'])->name('reservations.destroy');

    Route::post('reservations/{rental}/approve', [RentalController::class, 'approve'])->name('reservations.approve');
    Route::post('reservations/{rental}/release', [RentalController::class, 'release'])->name('reservations.release');
    Route::post('reservations/{rental}/return', [RentalController::class, 'return'])->name('reservations.return');
    Route::post('reservations/{rental}/cancel', [RentalController::class, 'cancel'])->name('reservations.cancel');
    Route::post('reservations/{rental}/extend', [RentalController::class, 'extend'])->name('reservations.extend');
});
});
