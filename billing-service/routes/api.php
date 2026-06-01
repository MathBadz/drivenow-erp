<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected invoice data.
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('invoices', [InvoiceController::class, 'index'])->name('api.v1.invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.v1.invoices.show');
});