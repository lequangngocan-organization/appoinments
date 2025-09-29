<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Anlqn\Appointments\Http\Controllers\{AppointmentController, GoogleOAuthController};

Route::prefix('api')->group(function () {
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::put('appointments/{id}',    [AppointmentController::class, 'update']);
    Route::delete('appointments/{id}', [AppointmentController::class, 'destroy']);
});

Route::prefix('auth')->group(function () {
    Route::get('google/redirect', [GoogleOAuthController::class, 'redirect']);
    Route::get('google/callback', [GoogleOAuthController::class, 'callback']);
});
