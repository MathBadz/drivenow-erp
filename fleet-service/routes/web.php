<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
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

Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

// Fleet changes are limited to administrators and front-desk staff.
Route::middleware('role:admin,staff')->group(function () {
    Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});
});
