<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PilgrimController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\YearController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/web/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('analytics')->group(function () {
        Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('income-expense', [AnalyticsController::class, 'incomeExpense']);
        Route::get('top-performers', [AnalyticsController::class, 'topPerformers']);
        Route::get('years', [AnalyticsController::class, 'getYears']);
    });

    require __DIR__ . '/web/group-leaders.php';
    require __DIR__ . '/web/pre-registrations.php';
    require __DIR__ . '/web/registrations.php';
    require __DIR__ . '/web/hajj-packages.php';
    require __DIR__ . '/web/umrahs.php';
    require __DIR__ . '/web/umrah-packages.php';
    require __DIR__ . '/web/sections.php';

    Route::prefix('transactions')->group(function () {
        Route::get('sections', [TransactionController::class, 'sections']);
        Route::get('pre-registrations', [TransactionController::class, 'preRegistrations']);
        Route::get('registrations', [TransactionController::class, 'registrations']);
        Route::get('loans', [TransactionController::class, 'loans']);

        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{transaction}', [TransactionController::class, 'show']);
        Route::put('/{transaction}', [TransactionController::class, 'update']);
    });

    // User profile routes
    Route::get('users', [UserController::class, 'index']);
    Route::get('user', [UserController::class, 'show']);
    Route::post('user/profile', [UserController::class, 'update']);
    Route::post('user/change-password', [UserController::class, 'changePassword']);

    Route::prefix('years')->group(function () {
        Route::get('/', [YearController::class, 'index']);
        Route::post('/', [YearController::class, 'store']);
        Route::put('/{year}', [YearController::class, 'update']);
    });

    Route::prefix('pilgrims')->group(function () {
        Route::get('/{type}/{id}', [PilgrimController::class, 'show']);
    });
});
