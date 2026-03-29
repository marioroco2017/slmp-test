<?php

use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PhotoController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\TodoController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (Sanctum token required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        Route::apiResource('users', UserController::class);
        Route::apiResource('posts', PostController::class);
        Route::apiResource('comments', CommentController::class);
        Route::apiResource('albums', AlbumController::class);
        Route::apiResource('photos', PhotoController::class);
        Route::apiResource('todos', TodoController::class);

        // Nested routes for related resources
        Route::get('users/{user}/posts', [UserController::class, 'posts']);
        Route::get('users/{user}/albums', [UserController::class, 'albums']);
        Route::get('users/{user}/todos', [UserController::class, 'todos']);
        Route::get('posts/{post}/comments', [PostController::class, 'comments']);
        Route::get('albums/{album}/photos', [AlbumController::class, 'photos']);
    });
});
