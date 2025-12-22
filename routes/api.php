<?php

use App\Http\Controllers\Api\BankSectionController;
use App\Http\Controllers\Api\BillSectionController;
use App\Http\Controllers\Api\EmployeeSectionController;
use App\Http\Controllers\Api\GroupLeaderSectionController;
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
            Route::put('/{section}', [BankSectionController::class, 'update']);
        });

        Route::prefix('group-leaders')->group(function () {
            Route::get('/', [GroupLeaderSectionController::class, 'index']);
            Route::post('/', [GroupLeaderSectionController::class, 'store']);
            Route::put('/{section}', [GroupLeaderSectionController::class, 'update']);
        });

        Route::prefix('employees')->group(function () {
            Route::get('/', [EmployeeSectionController::class, 'index']);
            Route::post('/', [EmployeeSectionController::class, 'store']);
            Route::put('/{section}', [EmployeeSectionController::class, 'update']);
        });

        Route::prefix('bills')->group(function () {
            Route::get('/', [BillSectionController::class, 'index']);
            Route::post('/', [BillSectionController::class, 'store']);
            Route::put('/{section}', [BillSectionController::class, 'update']);
        });

        Route::prefix("others")->group(function () {
            Route::get('/', [SectionController::class, 'index']);
            Route::post('/', [SectionController::class, 'store']);
            Route::put('/{section}', [SectionController::class, 'update']);
        });

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
