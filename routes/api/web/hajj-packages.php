<?php

use App\Http\Controllers\Api\HajjPackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('hajj-packages')->group(function () {
    Route::get('/', [HajjPackageController::class, 'index']);
    Route::post('/', [HajjPackageController::class, 'store']);
    Route::put('/{package}', [HajjPackageController::class, 'update']);
    Route::delete('/{package}', [HajjPackageController::class, 'destroy']);
    Route::get('/{package}/pilgrims', [HajjPackageController::class, 'pilgrims']);
});
