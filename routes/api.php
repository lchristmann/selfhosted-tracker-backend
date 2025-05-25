<?php

use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // User's own data
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/me', [UserController::class, 'updateMe']);
    Route::patch('/me', [UserController::class, 'updateMe']);
    Route::get('me/image', [UserController::class, 'imageMy']);
    Route::get('/me/locations', [LocationController::class, 'indexMy']);
    Route::post('/me/locations', [LocationController::class, 'storeMy']);

    // Other users' data
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/users/{user}/image', [UserController::class, 'image']);
    Route::get('/users/{user}/locations', [LocationController::class, 'indexByUser']);
});
