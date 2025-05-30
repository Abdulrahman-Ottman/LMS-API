<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LessonController;
Route::middleware(['auth:sanctum'])->group(function () {

// Lessons for instructors (create)
Route::prefix('courses/{course}/sections/{section}/lessons')->group(function () {
    Route::middleware(['role:instructor'])->group(function () {
        Route::post('/', [LessonController::class, 'store']); // Create a lesson under section
    });

    // Route::middleware(['role:student'])->group(function () {
    //     Route::get('/', [LessonController::class, 'index']); // Get all lessons in section (optional)
    // });

    // Lessons for students (view specific lesson)
    Route::middleware(['role:student'])->group(function () {
        Route::get('/{id}', [LessonController::class, 'show']);
        Route::post('/{id}/complete', [LessonController::class, 'completeLesson']);
//         Route::get('/{id}/stream', [LessonController::class, 'streamVideo']);
    });

    // Lessons for instructors (update, delete)
    Route::middleware(['role:instructor'])->group(function () {
        Route::put('/{id}', [LessonController::class, 'update']);
        Route::delete('/{id}', [LessonController::class, 'destroy']);
    });


});
});


