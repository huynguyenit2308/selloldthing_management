<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AddCategoryController;

Route::get('/', function () {
    return view('home');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [AddCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AddCategoryController::class, 'store'])->name('admin.categories.store');
});
