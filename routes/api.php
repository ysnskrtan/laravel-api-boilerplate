<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\V1\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check endpoint (public)
Route::get('/health', [HealthController::class, 'check']);

// File upload endpoints
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/upload/file', [FileController::class, 'uploadFile']);
    Route::post('/upload/image', [FileController::class, 'uploadImage']);
    Route::delete('/files/{filename}', [FileController::class, 'deleteFile']);
});

// User authentication endpoint
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// API v1 routes
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Authentication required routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // User management
        Route::apiResource('users', UserController::class);
        Route::get('users/with-roles', [UserController::class, 'withRoles']);
        
        // Posts management
        Route::apiResource('posts', PostController::class)->parameters([
            'posts' => 'slug'
        ]);
        
        // Additional post endpoints
        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('my-posts', [PostController::class, 'myPosts'])->name('my');
            Route::post('{slug}/publish', [PostController::class, 'publish'])->name('publish');
            Route::post('{slug}/archive', [PostController::class, 'archive'])->name('archive');
        });
    });
    
    // Public post endpoints
    Route::get('posts/published', [PostController::class, 'published'])->name('posts.published');
});

// Legacy routes (for backward compatibility)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('users/with-roles', [UserController::class, 'withRoles']);
});
