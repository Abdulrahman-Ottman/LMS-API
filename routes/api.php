<?php

require __DIR__.'/lessons.php';
require __DIR__.'/auth.php';
require __DIR__.'/students.php';
require __DIR__.'/instructors.php';
require __DIR__.'/categories.php';
require __DIR__.'/courses.php';
require __DIR__.'/sections.php';
require __DIR__.'/lessons.php';
require __DIR__.'/search.php';
require __DIR__.'/coupon.php';
require __DIR__.'/payments.php';
require __DIR__.'/videos.php';

// Authenticated routes
//Route::middleware(['auth:sanctum'])->group(function () {
//    // Profile/account management
//    Route::post('/password/change', [AuthController::class, 'changePassword']);
//    Route::post('/logout', [AuthController::class, 'logout']);
//
//    // Category selection
//    Route::middleware(['role:student'])->group(function () {
//        Route::post('/student/select-categories', [StudentController::class, 'attachMainCategories']);
//        Route::post('/student/select-subcategories', [StudentController::class, 'attachSubCategories']);
//    });
//
//    // Categories
//    Route::get('/categories/main', [CategoryController::class, 'getMainCategories']);
//    Route::get('/categories/{id}/subcategories', [CategoryController::class, 'getSubCategories']);
//
//    //Courses
//    Route::middleware(['role:student'])->group(function () {
//        Route::get('/courses', [CourseController::class, 'getCourses']);
//        Route::get('/courses/{id}', [CourseController::class, 'show']);
//        Route::get('/courses/{id}/view', [CourseController::class, 'addView']);
//        Route::post('/courses/{id}/rate', [CourseController::class, 'rate']);
//        Route::post('/courses/{id}/review', [CourseController::class, 'review']);
//        //sections
//        Route::get('/courses/{course}/sections', [SectionController::class, 'getAllSections']);
//        Route::get('/sections/{id}/lessons', [SectionController::class, 'lessons']);
//
//
//    });
//
//    // Instructor specific routes
//    Route::middleware(['role:instructor'])->group(function () {
//        Route::post('/courses', [CourseController::class, 'store']);
//        Route::post('/courses/{id}', [CourseController::class, 'update']);
//        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
//    });
//
//    //Instructor
//    Route::middleware(['role:student'])->group(function () {
//        Route::get('/instructors', [InstructorController::class, 'getInstructors']);
//        Route::get('/instructors/{id}', [InstructorController::class, 'show']);
//        Route::get('/instructors/{id}/view', [InstructorController::class, 'addView']);
//        Route::post('/instructors/{id}/rate', [InstructorController::class, 'rate']);
//    });
//
//    //Search
//    Route::middleware(['role:student'])->group(function () {
//        Route::get('/search', [SearchController::class, 'search']);
//        Route::get('/autocomplete', [SearchController::class, 'autoComplete']);
//    });
//
//
//    //sections (instructor routes)
//    Route::middleware(['role:instructor'])->group(function () {
//        Route::post('/sections', [SectionController::class, 'store']);
//        Route::put('/sections/{id}', [SectionController::class, 'update']);
//        Route::delete('/sections/{id}', [SectionController::class, 'destroy']);
//    });
//
//
//    //lessons
//    Route::prefix('courses/{course}/sections/{section}/lessons')->group(function () {
////        Route::middleware(['role:student'])->group(function () {
////            Route::get('/{id}', [LessonController::class, 'show']);
//////            Route::get('/{id}/stream', [LessonController::class, 'streamVideo']);
////        });
//        Route::middleware(['role:instructor'])->group(function () {
//            Route::post('/', [LessonController::class, 'store']);      // Create a lesson under section
//        });
//
//
//        Route::middleware(['role:instructor'])->group(function () {
//            Route::put('/{id}', [LessonController::class, 'update']);
//            Route::delete('/{id}', [LessonController::class, 'destroy']);
//        });
////        Route::middleware(['role:student'])->group(function () {
////            Route::get('/', [LessonController::class, 'index']);       // Get all lessons in section (optional)
////        });
//    });
