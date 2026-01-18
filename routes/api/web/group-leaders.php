<?php

use App\Http\Controllers\Api\GroupLeaderController;
use Illuminate\Support\Facades\Route;

Route::prefix('group-leaders')->group(function () {
    Route::get('/', [GroupLeaderController::class, 'index']);

    Route::get('/sections', [GroupLeaderController::class, 'sections']);
    Route::get('/pre-registrations', [GroupLeaderController::class, 'preRegistrations']);
    Route::post('/collection', [GroupLeaderController::class, 'collection']);
});
