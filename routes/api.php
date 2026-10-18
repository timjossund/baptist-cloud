<?php

use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Throttled inside the controller, so blocks answer with too_many_attempts.
    Route::post('auth/token', [TokenController::class, 'store'])->name('token.store');

    Route::middleware('auth:sanctum')->group(function () {
        // Sign out works even when the user is no longer verified.
        Route::delete('auth/token', [TokenController::class, 'destroy'])
            ->middleware('throttle:api-user')
            ->name('token.destroy');

        Route::middleware(['api.verified', 'throttle:api-user'])->group(function () {
            Route::get('me', MeController::class)->name('me');
        });
    });
});
