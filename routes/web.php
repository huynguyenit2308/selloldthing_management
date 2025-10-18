<?php

use App\Http\Controllers\CRUD_VoucherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AddCategoryController;
use App\Http\Controllers\UpdateCategoryController;
use App\Http\Controllers\ProductController;
Route::get('/', function () {
    return view('home');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [AddCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AddCategoryController::class, 'store'])->name('admin.categories.store');

    Route::get('/categories/{category}/edit', [UpdateCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [UpdateCategoryController::class, 'update'])->name('admin.categories.update');

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

    Route::fallback(function () {
        abort(404);
    });
});
