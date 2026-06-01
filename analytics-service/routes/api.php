<?php

use App\Http\Controllers\Api\SummaryController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

// Versioned, service-token-protected headline KPIs.
Route::prefix('v1')->middleware(VerifyServiceToken::class)->group(function () {
    Route::get('summary', SummaryController::class)->name('api.v1.summary');
});