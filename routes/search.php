<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/autocomplete', [SearchController::class, 'autoComplete']);
});
