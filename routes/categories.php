<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/categories/main', [CategoryController::class, 'getMainCategories']);
    Route::get('/categories/{id}/subcategories', [CategoryController::class, 'getSubCategories']);
});
