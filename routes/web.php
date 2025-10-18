<?php

use App\Http\Controllers\CRUD_VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});
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
