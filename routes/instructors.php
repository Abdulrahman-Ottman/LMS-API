<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstructorController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/instructors', [InstructorController::class, 'getInstructors']);
    Route::get('/instructors/{id}', [InstructorController::class, 'show']);
    Route::get('/instructors/{id}/view', [InstructorController::class, 'addView']);
    Route::post('/instructors/{id}/rate', [InstructorController::class, 'rate']);
});
Route::middleware(['auth:sanctum', 'role:instructor'])->group(function () {
    Route::post('/instructor/upload-cv', [InstructorController::class, 'uploadCv']);
});
