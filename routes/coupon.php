<?php

use App\Http\Controllers\CouponController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:instructor'])->group(function () {
        Route::post('/instructors/coupon', [CouponController::class, 'store']);
    });
    Route::middleware(['role:student'])->group(function () {
        Route::post('/courses/{course}/apply-coupon', [CouponController::class, 'applyCoupon']);
    });

});
