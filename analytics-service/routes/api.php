<?php

use App\Http\Controllers\Api\SummaryController;
use Illuminate\Support\Facades\Route;

// Headline KPIs for embedding in the Operations Hub or other dashboards.
Route::get('summary', SummaryController::class)->name('api.summary');
