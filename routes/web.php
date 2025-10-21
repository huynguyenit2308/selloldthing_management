<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\FacebookController;   
use App\Http\Controllers\Auth\ForgotPasswordController;

// Trang chủ
Route::get('/', function () {
    return view('home'); 
})->name('home');

// ===== AUTH =====

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Logout
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Register
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Google OAuth
Route::get('auth/google', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('auth/google/callback', function () {
    $user = Socialite::driver('google')->user();
    dd($user); // test tạm, sau này bạn save vào DB
});
// web.php
Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirect']);
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'callback']);
// ===== END AUTH =====

// Facebook OAuth
Route::get('auth/facebook', [FacebookController::class, 'redirect'])->name('facebook.redirect');
Route::get('auth/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');

//logout
Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('home');
})->name('logout');


// Password Reset Routes
Route::prefix('password')->group(function () {
    Route::get('/forgot', [ForgotPasswordController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot', [ForgotPasswordController::class, 'sendResetCode'])->name('password.sendCode');

    Route::get('/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verifyForm');
    Route::post('/verify', [ForgotPasswordController::class, 'verifyCode'])->name('password.verify');

    Route::get('/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.resetForm');
    Route::post('/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
});