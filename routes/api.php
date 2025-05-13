<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/verifyRegister' , [AuthController::class , 'verifyRegister']);
Route::post('/login', [AuthController::class, 'login']);
Route::Post('/auth/google', [AuthController::class, 'googleSignIn']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/student/select-categories', [StudentController::class, 'attachMainCategories']);
    Route::post('/student/select-subcategories', [StudentController::class, 'attachSubCategories']);
});
