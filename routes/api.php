<?php

use App\Http\Controllers\Api\BankSectionController;
use App\Http\Controllers\Api\BillSectionController;
use App\Http\Controllers\Api\BorrowingSectionController;
use App\Http\Controllers\Api\EmployeeSectionController;
use App\Http\Controllers\Api\GroupLeaderController;
use App\Http\Controllers\Api\GroupLeaderSectionController;
use App\Http\Controllers\Api\HajjPackageController;
use App\Http\Controllers\Api\UmrahPackageController;
use App\Http\Controllers\Api\LendingSectionController;
use App\Http\Controllers\Api\PreRegistrationController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\YearController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('group-leaders')->group(function () {
        Route::get('/', [GroupLeaderController::class, 'index']);
        Route::post('/', [GroupLeaderController::class, 'store']);
        Route::put('/{groupLeader}', [GroupLeaderController::class, 'update']);
        Route::delete('/{groupLeader}', [GroupLeaderController::class, 'destroy']);
    });

    Route::get('pre-registrations/group-leaders', [PreRegistrationController::class, 'groupLeaders']);
    Route::get('pre-registrations/banks', [PreRegistrationController::class, 'banks']);

    Route::prefix('pre-registrations')->group(function () {
        Route::get('/', [PreRegistrationController::class, 'index']);
        Route::post('/', [PreRegistrationController::class, 'store']);
        Route::put('/{preRegistration}', [PreRegistrationController::class, 'update']);
        Route::delete('/{preRegistration}', [PreRegistrationController::class, 'destroy']);
    });

    Route::prefix('hajj-packages')->group(function () {
        Route::get('/', [HajjPackageController::class, 'index']);
        Route::post('/', [HajjPackageController::class, 'store']);
        Route::put('/{package}', [HajjPackageController::class, 'update']);
        Route::delete('/{package}', [HajjPackageController::class, 'destroy']);
    });
    Route::prefix('umrah-packages')->group(function () {
        Route::get('/', [UmrahPackageController::class, 'index']);
        Route::post('/', [UmrahPackageController::class, 'store']);
        Route::put('/{package}', [UmrahPackageController::class, 'update']);
        Route::delete('/{package}', [UmrahPackageController::class, 'destroy']);
    });

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

        Route::prefix('loans')->group(function () {
            Route::prefix('lendings')->group(function () {
                Route::get('/', [LendingSectionController::class, 'index']);
                Route::post('/', [LendingSectionController::class, 'store']);
                Route::put('/{lending}', [LendingSectionController::class, 'update']);
                Route::delete('/{lending}', [LendingSectionController::class, 'destroy']);
            });

            Route::prefix('borrowings')->group(function () {
                Route::get('/', [BorrowingSectionController::class, 'index']);
                Route::post('/', [BorrowingSectionController::class, 'store']);
                Route::put('/{borrowing}', [BorrowingSectionController::class, 'update']);
                Route::delete('/{borrowing}', [BorrowingSectionController::class, 'destroy']);
            });
        });
    });

    // User profile routes
    Route::get('users', [UserController::class, 'index']);
    Route::put('user', [UserController::class, 'update']);
    Route::post('user/change-password', [UserController::class, 'changePassword']);

    Route::prefix('years')->group(function () {
        Route::get('/', [YearController::class, 'index']);
        Route::post('/', [YearController::class, 'store']);
        Route::put('/{year}', [YearController::class, 'update']);
    });
});
