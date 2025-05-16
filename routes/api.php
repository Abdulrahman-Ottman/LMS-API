<?php
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
});
