<?php

use App\Http\Controllers\CRUD_VoucherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\ProductController;
Route::get('/', function () {
    return view('home');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
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
