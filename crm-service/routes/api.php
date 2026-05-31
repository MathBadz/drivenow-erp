<?php

use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;

// Cross-service customer data.
Route::get('customers', [CustomerController::class, 'index'])->name('api.customers.index');
Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('api.customers.show');
