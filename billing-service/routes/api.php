<?php

use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Support\Facades\Route;

// Cross-service invoice data.
Route::get('invoices', [InvoiceController::class, 'index'])->name('api.invoices.index');
Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.invoices.show');
