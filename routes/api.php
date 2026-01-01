<?php

use App\Http\Controllers\Api\AnalyticsController;
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
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UmrahController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\YearController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/api/auth.php';

Route::get('/health-check', function () {
    try {
        $dbConnected = false;
        $dbError = null;

        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        $dbInfo = db_info();

        return response()->json([
            'status' => 'alive',
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ],
            'database' => array_merge([
                'connected' => $dbConnected,
                'error' => $dbError
            ], $dbInfo),
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp' => now()->toIso8601String(),
                'timezone' => date_default_timezone_get(),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('analytics')->group(function () {
        Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('income-expense', [AnalyticsController::class, 'incomeExpense']);
        Route::get('top-performers', [AnalyticsController::class, 'topPerformers']);
        Route::get('years', [AnalyticsController::class, 'getYears']);
    });

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

    Route::prefix('registrations')->group(function () {
        Route::get('banks', [RegistrationController::class, 'banks']);
        Route::get('pre-registrations', [RegistrationController::class, 'preRegistrations']);
        Route::get('packages', [RegistrationController::class, 'packages']);
        Route::get('/', [RegistrationController::class, 'index']);
        Route::post('/', [RegistrationController::class, 'store']);
        Route::put('/{registration}', [RegistrationController::class, 'update']);
        Route::delete('/{registration}', [RegistrationController::class, 'destroy']);
    });

    Route::prefix('hajj-packages')->group(function () {
        Route::get('/', [HajjPackageController::class, 'index']);
        Route::post('/', [HajjPackageController::class, 'store']);
        Route::put('/{package}', [HajjPackageController::class, 'update']);
        Route::delete('/{package}', [HajjPackageController::class, 'destroy']);
    });

    Route::prefix('umrahs')->group(function () {
        Route::get('packages', [UmrahController::class, 'packages']);
        Route::get('group-leaders', [UmrahController::class, 'groupLeaders']);
        Route::get('pilgrims', [UmrahController::class, 'pilgrims']);
        Route::get('/', [UmrahController::class, 'index']);
        Route::post('/', [UmrahController::class, 'store']);
        Route::put('/{umrah}', [UmrahController::class, 'update']);
        Route::delete('/{umrah}', [UmrahController::class, 'destroy']);
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
            Route::get('/{section}', [BankSectionController::class, 'show']);
            Route::put('/{section}', [BankSectionController::class, 'update']);
            Route::get('/{section}/transactions', [BankSectionController::class, 'transactions']);
        });

        Route::prefix('group-leaders')->group(function () {
            Route::get('/', [GroupLeaderSectionController::class, 'index']);
            Route::post('/', [GroupLeaderSectionController::class, 'store']);
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
});
