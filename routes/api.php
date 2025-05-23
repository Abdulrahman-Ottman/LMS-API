<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CategoryController;

// Public routes
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/verify', [AuthController::class, 'verifyRegister']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'googleSignIn']);

    // Reset & password-related
    Route::post('/password/forgot', [AuthController::class, 'sendResetCode']); // Send reset code
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);    // Set new password
});

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Profile/account management
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Category selection
    Route::middleware(['role:student'])->group(function () {
        Route::post('/student/select-categories', [StudentController::class, 'attachMainCategories']);
        Route::post('/student/select-subcategories', [StudentController::class, 'attachSubCategories']);
    });

    // Categories
    Route::get('/categories/main', [CategoryController::class, 'getMainCategories']);
    Route::get('/categories/{id}/subcategories', [CategoryController::class, 'getSubCategories']);

    //Courses
    Route::middleware(['role:student'])->group(function () {
        Route::get('/courses', [CourseController::class, 'getCourses']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::get('/courses/{id}/view', [CourseController::class, 'addView']);
        Route::post('/courses/{id}/rate', [CourseController::class, 'rate']);
        Route::post('/courses/{id}/review', [CourseController::class, 'review']);
    });

    // Instructor specific routes
    Route::middleware(['role:instructor'])->group(function () {
        Route::post('/courses', [CourseController::class, 'store']);
        Route::post('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    });

    //Instructor
    Route::middleware(['role:student'])->group(function () {
        Route::get('/instructors', [InstructorController::class, 'getInstructors']);
        Route::get('/instructors/{id}', [InstructorController::class, 'show']);
        Route::get('/instructors/{id}/view', [InstructorController::class, 'addView']);
        Route::post('/instructors/{id}/rate', [InstructorController::class, 'rate']);
    });

    //Search
    Route::middleware(['role:student'])->group(function () {
        Route::get('/search', [SearchController::class, 'search']);
        Route::get('/autocomplete', [SearchController::class, 'autoComplete']);
    });
});
