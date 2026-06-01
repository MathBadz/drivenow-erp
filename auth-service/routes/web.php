<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\SystemController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// The Operations Hub never shows a public landing page — go straight to login.
Route::redirect('/', '/login')->name('home');

// SSO bounce (issuer side). A sibling service on a different host (e.g. each
// *.onrender.com domain) redirects unauthenticated visitors here. If the hub
// session is valid we hand the signed token back to that service's /sso endpoint
// so it can establish its own cookie; otherwise we send the user to log in first
// and resume the bounce afterwards. Only known sibling origins are allowed.
Route::get('sso/bounce', function (Request $request) {
    $return = (string) $request->query('return', '');
    $parts = parse_url($return) ?: [];
    $base = (isset($parts['scheme'], $parts['host']))
        ? $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '')
        : '';
    $path = ($parts['path'] ?? '/dashboard').(isset($parts['query']) ? '?'.$parts['query'] : '');

    $allowed = collect(config('services.public', []))
        ->map(fn ($u) => rtrim((string) $u, '/'))
        ->contains($base);

    $token = (string) $request->cookie('drivenow_token');

    if ($allowed && $token !== '' && app(\App\Services\JwtService::class)->verify($token)) {
        return redirect($base.'/sso?t='.urlencode($token).'&next='.urlencode($path));
    }

    if ($allowed) {
        $request->session()->put('url.intended', $request->fullUrl());
    }

    return redirect('/login');
})->name('sso.bounce');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Friendly landing when the hub detects an offline service — the user is
    // routed here (instead of a dead link) and prompted back to the hub.
    Route::get('service-unavailable', fn (Request $request) => Inertia::render('ServiceUnavailable', [
        'service' => $request->string('service')->toString() ?: 'That service',
        'url' => $request->string('url')->toString() ?: null,
    ]))->name('service.unavailable');

    // Global system settings + user management — administrators only.
    Route::middleware('role:admin')->group(function () {
        Route::get('settings/system', [SystemController::class, 'edit'])->name('system.edit');
        Route::put('settings/system', [SystemController::class, 'update'])->name('system.update');

        Route::get('settings/users', [UserController::class, 'index'])->name('users.index');
        Route::post('settings/users', [UserController::class, 'store'])->name('users.store');
        Route::put('settings/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('settings/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__.'/settings.php';
