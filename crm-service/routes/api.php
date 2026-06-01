<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected customer data.
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('customers', [CustomerController::class, 'index'])->name('api.v1.customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('api.v1.customers.show');
});