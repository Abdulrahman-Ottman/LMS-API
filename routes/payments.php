<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('/payments/intent', [PaymentController::class, 'intent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirm']);
});
