<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaxController::class, 'index'])->name('home');

// Handle tax query submission
Route::post('/ask', [TaxController::class, 'ask'])->name('ask');

// Get detailed analysis for a query
Route::post('/detailed', [TaxController::class, 'detailed'])->name('detailed');

Route::post('/subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
