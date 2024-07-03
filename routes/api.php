<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\UserController;
use App\Http\Controllers\API\Auth\VerificationController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// User authentication
Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/logout', [LoginController::class, 'logout']);
        Route::get('/user/info', [UserController::class, 'info']);

        if (Features::enabled(Features::emailVerification())) {
            Route::get('/email/verify/{id}', [VerificationController::class, 'verify']);
            Route::get('/email/resend', [VerificationController::class, 'resend']);
        }
    });
});
