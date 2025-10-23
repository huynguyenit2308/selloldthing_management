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


// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [AddCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AddCategoryController::class, 'store'])->name('admin.categories.store');
});
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// Danh sách voucher
Route::get('voucher/list', [CRUD_VoucherController::class, 'listvoucher'])->name('voucher.list');
// Chi tiết voucher
Route::get('voucher/detail', [CRUD_VoucherController::class, 'detailVoucher'])->name('voucher.detail');
// Thêm voucher
Route::get('voucher/add', [CRUD_VoucherController::class, 'addvoucher'])->name('voucher.add');
Route::post('voucher/add', [CRUD_VoucherController::class, 'postAddvoucher'])->name('voucher.store');
// Sửa voucher
Route::get('voucher/update', [CRUD_VoucherController::class, 'updatevoucher'])->name('voucher.edit');
Route::post('voucher/update', [CRUD_VoucherController::class, 'updatePostvoucher'])->name('voucher.update');
// Xóa voucher
Route::delete('voucher/delete', [CRUD_VoucherController::class, 'deleteVoucher'])->name('voucher.delete');

// Route fallback cho mọi GET không hợp lệ
Route::fallback(function () {
    abort(404);
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
Route::get('login/{provider}', [SocialController::class, 'redirectToProvider']);
Route::get('login/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

