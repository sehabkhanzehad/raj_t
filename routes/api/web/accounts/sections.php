<?php

use App\Http\Controllers\Api\BillSectionController;
use App\Http\Controllers\Api\BorrowingSectionController;
use App\Http\Controllers\Api\EmployeeSectionController;
use App\Http\Controllers\Api\GroupLeaderSectionController;
use App\Http\Controllers\Api\LendingSectionController;
use App\Http\Controllers\Api\SectionController;
use Illuminate\Support\Facades\Route;

Route::prefix('sections')->group(function () {

    require __DIR__ . '/sections/banks.php';



    Route::prefix('group-leaders')->group(function () {
        Route::get('/', [GroupLeaderSectionController::class, 'index']);
        Route::post('/', [GroupLeaderSectionController::class, 'store']);
        Route::get('/{section}', [GroupLeaderSectionController::class, 'show']);
        Route::put('/{section}', [GroupLeaderSectionController::class, 'update']);
        Route::get('/{section}/transactions', [GroupLeaderSectionController::class, 'transactions']);
    });

    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeSectionController::class, 'index']);
        Route::post('/', [EmployeeSectionController::class, 'store']);
        Route::put('/{section}', [EmployeeSectionController::class, 'update']);
        Route::get('/{section}/transactions', [EmployeeSectionController::class, 'transactions']);
    });

    Route::prefix('bills')->group(function () {
        Route::get('/', [BillSectionController::class, 'index']);
        Route::post('/', [BillSectionController::class, 'store']);
        Route::get('/{section}', [BillSectionController::class, 'show']);
        Route::put('/{section}', [BillSectionController::class, 'update']);
        Route::get('/{section}/transactions', [BillSectionController::class, 'transactions']);
    });

    Route::prefix("others")->group(function () {
        Route::get('/', [SectionController::class, 'index']);
        Route::post('/', [SectionController::class, 'store']);
        Route::get('/{section}', [SectionController::class, 'show']);
        Route::put('/{section}', [SectionController::class, 'update']);
        Route::get('/{section}/transactions', [SectionController::class, 'transactions']);
    });

    Route::delete('/{section}', [SectionController::class, 'destroy']);

    Route::prefix('loans')->group(function () {
        Route::prefix('lendings')->group(function () {
            Route::get('/', [LendingSectionController::class, 'index']);
            Route::post('/', [LendingSectionController::class, 'store']);
            Route::get('/{loan}', [LendingSectionController::class, 'show']);
            Route::get('/{loan}/transactions', [LendingSectionController::class, 'transactions']);
        });

        Route::prefix('borrowings')->group(function () {
            Route::get('/', [BorrowingSectionController::class, 'index']);
            Route::post('/', [BorrowingSectionController::class, 'store']);
            Route::get('/{loan}', [LendingSectionController::class, 'show']);
            Route::get('/{loan}/transactions', [LendingSectionController::class, 'transactions']);
        });
    });
});
