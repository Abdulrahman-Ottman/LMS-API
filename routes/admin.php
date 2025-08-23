<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/pending-cvs', [AdminController::class, 'pendingCvs']);
    Route::post('/courses/{course}/disable', [CourseController::class, 'disable']);
    Route::post('/instructor/{instructor}/courses/disable', [CourseController::class, 'disableAll']);
});
