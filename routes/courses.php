<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:student'])->group(function () {
        Route::get('/courses', [CourseController::class, 'getCourses']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::get('/courses/{id}/view', [CourseController::class, 'addView']);
        Route::post('/courses/{id}/rate', [CourseController::class, 'rate']);
        Route::post('/courses/{id}/review', [CourseController::class, 'review']);
    });

    Route::middleware(['role:instructor'])->group(function () {
        Route::post('/courses', [CourseController::class, 'store']);
        Route::post('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    });
});
