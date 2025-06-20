<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('videos')->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::get('{filename}/link', [VideoController::class, 'getVideoLink']);
//        Route::get('stream/{filename}', [VideoController::class, 'streamVideo'])->name('stream.video');
    });
        Route::prefix('subtitles')->group(function () {
            Route::get('{filename}/languages', [VideoController::class, 'getSubtitlesLanguages']);
//            Route::get('{filename}', [VideoController::class, 'getSubtitles']);

            Route::middleware('role:instructor')->group(function () {
                Route::post('{id}', [VideoController::class, 'addSubtitles']);
                Route::delete('{id}/{lang}', [VideoController::class, 'deleteSubtitles']);
            });
        });
});
Route::get('videos/stream/{filename}', [VideoController::class, 'streamVideo'])->name('stream.video');
Route::get('videos/subtitles/{filename}', [VideoController::class, 'getSubtitles']);
