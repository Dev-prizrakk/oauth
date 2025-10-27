<?php
// Base
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthPageController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\EmailVerificationFrontendController;

// Главная страница
Route::get('/', function () {
    return view('home');
});

// Авторизация
Route::get('/auth', [AuthPageController::class, 'index'])->name('auth.page');
Route::post('/auth/login', [AuthPageController::class, 'login'])->name('auth.login');
Route::post('/auth/logout', [AuthPageController::class, 'logout'])->name('auth.logout');

// Регистрация
Route::get('/register', [AuthPageController::class, 'registerForm'])->name('auth.register.page');
Route::post('/register', [AuthPageController::class, 'register'])->name('auth.register');

// Профиль (только для авторизованных)
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile');


Route::post('/profile/email/send', [EmailVerificationFrontendController::class, 'send'])->name('email.send')->middleware('auth');
Route::post('/profile/email-verify', [EmailVerificationFrontendController::class, 'verify'])
    ->middleware('auth')
    ->name('email.verify.frontend');


