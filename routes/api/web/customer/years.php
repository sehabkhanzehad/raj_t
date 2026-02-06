<?php

use App\Http\Controllers\Api\Web\Customer\YearController;
use Illuminate\Support\Facades\Route;

Route::prefix('years')->group(function () {
    Route::get('/', [YearController::class, 'index']);
    Route::post('/', [YearController::class, 'store']);
    Route::put('/{year}', [YearController::class, 'update']);
    Route::put('/{year}/default', [YearController::class, 'updateDefault']);
});
