<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
Route::get('/', function () {
    return view('home');
});

Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');