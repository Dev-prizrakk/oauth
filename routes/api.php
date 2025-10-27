<?php
// Base
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\EmailVerificationController;


// API Маршруты версии 1 (аутентификация, пароль, подтверждение email)
Route::prefix('/v1/auth')->group(function () {
    // Аутентификация
    Route::post('/registration', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Маршруты, требующие авторизации
    Route::middleware('auth.api')->group(function () {
        Route::delete('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Работа с паролем
    Route::post('/password/forgot', [PasswordController::class, 'forgot']);
    Route::post('/password/reset', [PasswordController::class, 'reset']);
    Route::post('/password/token-check', [PasswordController::class, 'tokenCheck']);

    // Подтверждение email
    Route::post('/email-verify/{user}', [EmailVerificationController::class, 'sendLink']);
    Route::post('/email-verify', [EmailVerificationController::class, 'verify']);
});
