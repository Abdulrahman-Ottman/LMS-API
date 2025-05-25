<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SectionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:student'])->group(function () {
        Route::get('/courses/{course}/sections', [SectionController::class, 'getAllSections']);
        Route::get('/sections/{id}/lessons', [SectionController::class, 'lessons']);
    });

    Route::middleware(['role:instructor'])->group(function () {
        Route::post('/sections', [SectionController::class, 'store']);
        Route::put('/sections/{id}', [SectionController::class, 'update']);
        Route::delete('/sections/{id}', [SectionController::class, 'destroy']);
    });
});
