<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('/student/select-categories', [StudentController::class, 'attachMainCategories']);
    Route::post('/student/select-subcategories', [StudentController::class, 'attachSubCategories']);
});
