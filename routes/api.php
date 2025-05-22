<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CategoryController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/verify', [AuthController::class, 'verifyRegister']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleSignIn']);

// Reset & password-related
Route::post('/password/forgot', [AuthController::class, 'sendResetCode']); // Send reset code
Route::post('/password/reset', [AuthController::class, 'resetPassword']);    // Set new password

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Profile/account management
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Category selection
    Route::post('/student/select-categories', [StudentController::class, 'attachMainCategories']);
    Route::post('/student/select-subcategories', [StudentController::class, 'attachSubCategories']);

    // Categories
    Route::get('/categories/main', [CategoryController::class, 'getMainCategories']);
    Route::get('/categories/{id}/subcategories', [CategoryController::class, 'getSubCategories']);

    //Courses
    Route::get('/courses', [CourseController::class, 'getCourses']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    Route::get('/courses/{id}/view', [CourseController::class, 'addView']);

    //Instructor
    Route::get('/instructors', [InstructorController::class, 'getInstructors']);
    Route::get('/instructors/{id}', [InstructorController::class, 'show']);

    //Search
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/autocomplete', [SearchController::class, 'autoComplete']);

});
