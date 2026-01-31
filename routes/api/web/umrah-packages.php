<?php

use App\Http\Controllers\Api\UmrahPackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('umrah-packages')->group(function () {
    Route::get('/', [UmrahPackageController::class, 'index']);
    Route::post('/', [UmrahPackageController::class, 'store']);
    Route::get('/{package}', [UmrahPackageController::class, 'show']);
    Route::put('/{package}', [UmrahPackageController::class, 'update']);
    Route::delete('/{package}', [UmrahPackageController::class, 'destroy']);



    Route::get('/{package}/pilgrims', [UmrahPackageController::class, 'pilgrims']);

    Route::get('/{package}/pilgrims-for-collection', [UmrahPackageController::class, 'pilgrimsForCollection']);
    Route::post('/{package}/pilgrims/collection', [UmrahPackageController::class, 'collection']);
});
