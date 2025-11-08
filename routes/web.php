<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\FacebookController;   
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\DashboardController;

// Trang chủ
Route::get('/', function () {
    return view('home'); 
})->name('home');

// ===== AUTH =====

// Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
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

//thong tin tai khoan
Route::middleware(['auth'])->group(function () {
    // Thông tin tài khoản
    Route::get('/account/info', [AccountController::class, 'info'])->name('account.info');

    // Đổi mật khẩu
    Route::get('/account/change-password', [AccountController::class, 'showChangePassword'])->name('account.change');
    Route::post('/account/change-password', [AccountController::class, 'updatePassword'])->name('account.updatePassword');
    Route::post('/account/confirm-logout', [AccountController::class, 'confirmLogoutAfterChange'])->name('account.confirmLogout');
    // Xóa tài khoản
    Route::post('/account/delete', [AccountController::class, 'deleteAccount'])->name('account.delete');
});









































//thong tin ca nhan 

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});


