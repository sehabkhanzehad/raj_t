<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/sign-in', [AuthController::class, 'signIn']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sign-out', [AuthController::class, 'signOut']);
    });
});
