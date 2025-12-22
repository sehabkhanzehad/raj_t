<?php

use App\Http\Controllers\Api\BankSectionController;
use App\Http\Controllers\Api\GroupLeaderController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\YearController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('sections')->group(function () {
        Route::prefix('banks')->group(function () {
            Route::get('/', [BankSectionController::class, 'index']);
            Route::post('/', [BankSectionController::class, 'store']);
        });

        // Route::prefix('group-leaders')->group(function () {
        //     Route::get('/', [GroupLeaderController::class, 'index']);
        //     Route::post('/', [GroupLeaderController::class, 'store']);
        //     Route::put('/{groupLeader}', [GroupLeaderController::class, 'update']);
        //     Route::delete('/{groupLeader}', [GroupLeaderController::class, 'destroy']);
        // });

        Route::put('/{section}', [SectionController::class, 'update']);
        Route::delete('/{section}', [SectionController::class, 'destroy']);
    });


    // User profile routes
    Route::put('user', [UserController::class, 'update']);
    Route::post('user/change-password', [UserController::class, 'changePassword']);

    Route::prefix('years')->group(function () {
        Route::get('/', [YearController::class, 'index']);
        Route::post('/', [YearController::class, 'store']);
        Route::put('/{year}', [YearController::class, 'update']);
    });
});
