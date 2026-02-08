<?php

use App\Http\Controllers\Api\PreRegistrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('pre-registrations')->group(function () {
    Route::get('group-leaders', [PreRegistrationController::class, 'groupLeaders']); // ✅
    Route::get('pilgrims', [PreRegistrationController::class, 'pilgrims']); // ✅
    Route::get('passports', [PreRegistrationController::class, 'passports']);

    Route::get('/', [PreRegistrationController::class, 'index']); // ✅
    Route::get('/archived', [PreRegistrationController::class, 'archived']); // ✅
    Route::post('/', [PreRegistrationController::class, 'store']); // ✅
    Route::get('/{preRegistration}', [PreRegistrationController::class, 'show']);
    Route::delete('/{preRegistration}', [PreRegistrationController::class, 'destroy']);

    // Pilgrim update routes
    Route::put('/{preRegistration}/pilgrim/personal-info', [PreRegistrationController::class, 'updatePilgrimPersonalInfo']);
    Route::put('/{preRegistration}/pilgrim/contact-info', [PreRegistrationController::class, 'updatePilgrimContactInfo']);
    Route::put('/{preRegistration}/pilgrim/addresses', [PreRegistrationController::class, 'updateAddresses']);
    Route::post('/{preRegistration}/pilgrim/avatar', [PreRegistrationController::class, 'updatePilgrimAvatar']);
    Route::put('/{preRegistration}/pilgrim/update-pre-registration', [PreRegistrationController::class, 'updatePreRegDetails']);

    Route::put('/{preRegistration}/active', [PreRegistrationController::class, 'activePreRegistration']);
    Route::put('/{preRegistration}/cancel', [PreRegistrationController::class, 'cancelPreRegistration']);
    Route::put('/{preRegistration}/archive', [PreRegistrationController::class, 'archivePreRegistration']);
    Route::put('/{preRegistration}/transfer', [PreRegistrationController::class, 'transferPreRegistration']);

    // Passport routes
    Route::post('/{preRegistration}/passport', [PreRegistrationController::class, 'addPassport']);
    Route::put('/passport/{passport}', [PreRegistrationController::class, 'updatePassport']);

    Route::get('/{preRegistration}/transactions', [PreRegistrationController::class, 'transactions']);
});
