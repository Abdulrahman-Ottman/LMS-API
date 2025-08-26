<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/pending-cvs', [AdminController::class, 'pendingCvs']);
    Route::post('/courses/{course}/disable', [CourseController::class, 'disable']);
    Route::post('/instructor/{instructor}/courses/disable', [CourseController::class, 'disableAll']);

    Route::post('/instructors/{instructor}/enable', [InstructorController::class, 'enable']);
    Route::post('/instructors/{instructor}/disable', [InstructorController::class, 'disable']);

    Route::post('/instructors/{instructor}/accept-cv', [InstructorController::class, 'acceptCv']);
    Route::post('/instructors/{instructor}/reject-cv', [InstructorController::class, 'rejectCv']);
    Route::get('/dashboard/admin', [AdminController::class, 'adminDashboard']);


    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

});
