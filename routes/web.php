<?php

use App\Http\Controllers\CRUD_VoucherController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AddCategoryController;
use App\Http\Controllers\UpdateCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryStatisticsController;
use App\Http\Controllers\AuthController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\CRUD_InvoiceController;
use App\Http\Controllers\CRUD_OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CategoryWatchlistController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FavoriteController;

// Trang chủ

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Danh sách danh mục
Route::get('/categories', [CategoryController::class, 'indexFrontend'])->name('categories.index');
// Xem danh mục
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Search
Route::get('/search', [SearchController::class, 'page'])->name('search.index');
Route::prefix('search')->group(function () {
    Route::get('/bootstrap', [SearchController::class, 'bootstrap'])->name('search.bootstrap');
    Route::get('/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
    Route::get('/results', [SearchController::class, 'results'])->name('search.results');
    Route::get('/history', [SearchController::class, 'history'])->name('search.history.index');
    Route::delete('/history', [SearchController::class, 'clearHistory'])->name('search.history.clear');
    Route::delete('/history/{history}', [SearchController::class, 'destroyHistory'])->name('search.history.destroy');
    Route::post('/products/{product}/favorite', [SearchController::class, 'toggleFavorite'])->name('search.favorite.toggle');
    Route::post('/products/{product}/cart', [SearchController::class, 'addToCart'])->name('search.cart.add');
});

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

// Admin routes danh mục
Route::prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [AddCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AddCategoryController::class, 'store'])->name('admin.categories.store');
    //sửa danh mục
    Route::get('/categories/{category}/edit', [UpdateCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [UpdateCategoryController::class, 'update'])->name('admin.categories.update');
    //xóa danh mục
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    //thống kê danh mục
    Route::get('/statistics/categories', [CategoryStatisticsController::class, 'index'])->name('admin.statistics.categories');
    Route::get('/statistics/categories/export', [CategoryStatisticsController::class, 'exportExcel'])->name('admin.statistics.categories.export');
    Route::get('/statistics/categories/print', [CategoryStatisticsController::class, 'printReport'])->name('admin.statistics.categories.print');
    Route::get('/statistics/categories/chart-data', [CategoryStatisticsController::class, 'getChartData'])->name('admin.statistics.categories.chart-data');
    // Admin routes quản lý sản phẩm
    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');

    // Admin product actions
    Route::post('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('admin.products.approve');
    Route::post('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('admin.products.reject');
    Route::post('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('admin.products.toggleFeatured');
    Route::post('/products/{product}/update-status', [AdminProductController::class, 'updateStatus'])->name('admin.products.updateStatus');
    Route::post('/products/{product}/extend', [AdminProductController::class, 'extendExpiration'])->name('admin.products.extend');
});
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::middleware('auth')->group(function () {
    Route::get('/my-products', [ProductController::class, 'manage'])->name('products.manage');
    Route::get('/my-products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/my-products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/my-products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/my-products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/my-products/{product}/check-delete', [ProductController::class, 'checkDeleteConditions'])->name('products.checkDelete');
    Route::get('/product-not-found', [ProductController::class, 'productNotFound'])->name('products.notFound');
    Route::delete('/my-products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/my-products/undo-delete', [ProductController::class, 'undoDelete'])->name('products.undoDelete');
    Route::post('/my-products/bulk', [ProductController::class, 'bulkAction'])->name('products.bulk');
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
Route::middleware('auth')->group(function () {
    Route::get('order/list', [CRUD_OrderController::class, 'listOrder'])->name('orders.list');
});
// Thanh toán
Route::get('payment', [PaymentController::class, 'showPayment'])->name('order.payment');
Route::post('payment/precess', [PaymentController::class, 'paymentCashAndOnline'])->name('payment.cash.online');
// Thanh toán momo
Route::get('momo/callback', [PaymentController::class, 'momoCallback'])->name('momo.callback');
// Thanh toán vnpay
Route::get('vnpay/callback', [PaymentController::class, 'vnpayCallback'])->name('vnpay.callback');
// Thêm vào giỏ hàng
Route::post('/cart/add', [CRUD_OrderController::class, 'addToCart'])->name('cart.add');
// Danh sách hóa đơn
Route::get('invoice/list', [CRUD_InvoiceController::class, 'listInvoice'])->name('invoice.list');
// Chi tiết hóa đơn
Route::get('invoice/detail', [CRUD_InvoiceController::class, 'detailInvoice'])->name('invoice.detail');
// Hủy đơn hàng
Route::post('order/cancel/{encodeId}', [CRUD_OrderController::class, 'cancelOrder'])->name('order.cancel')->middleware('auth');
// ChatBot
Route::post('/chat/send', [ChatBotController::class, 'send'])->name('chat.send');
// In PDF
Route::get('/invoice/pdf/{id}', [CRUD_InvoiceController::class, 'generatePDF'])->name('invoice.pdf');
// Route fallback cho mọi GET không hợp lệ
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

    // Quản lý tồn kho
    Route::get('/account/inventory', [InventoryController::class, 'index'])->name('account.inventory');
    Route::get('/account/inventory/export', [InventoryController::class, 'export'])->name('account.inventory.export');
    Route::patch('/account/inventory/{product}', [InventoryController::class, 'updateStock'])->name('account.inventory.update');
    Route::post('/account/inventory/bulk', [InventoryController::class, 'bulkSave'])->name('account.inventory.bulk');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Category Watchlist Routes
    Route::get('/watchlist', [CategoryWatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist/{category}', [CategoryWatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{category}', [CategoryWatchlistController::class, 'destroy'])->name('watchlist.destroy');
    Route::post('/watchlist/{category}/toggle', [CategoryWatchlistController::class, 'toggle'])->name('watchlist.toggle');

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
});




// 🟦 Lưu bình luận cho review
Route::post('/reviews/{reviewId}/comments', [CommentController::class, 'store'])
    ->name('comments.store');

// 🟨 Lưu phản hồi cho comment
Route::post('/comments/{comment}/reply', [CommentController::class, 'reply'])
    ->name('comments.reply');

// 🟩 Hiển thị chi tiết review + toàn bộ comment (nếu cần)
Route::get('/reviews/{reviewId}/comments', [CommentController::class, 'show'])
    ->name('comments.show');

// 🟩 Route để CẬP NHẬT (Sửa/PUT) một comment
Route::put('/comments/{comment}', [CommentController::class, 'update'])
    ->name('comments.update')
    ->middleware('auth');

// 🟥 Route để XÓA (DELETE) một comment
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy')
    ->middleware('auth');





// Hiển thị chi tiết sản phẩm + danh sách đánh giá
Route::get('/product/{id}', [ReviewController::class, 'showReviews'])->name('product.show');
Route::get('/manage', [ReviewController::class, 'showReviews'])->name('product.show');
// Thêm đánh giá mới (AJAX)
Route::post('/product/{id}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Lấy dữ liệu để sửa đánh giá (AJAX)
Route::get('/reviews/{id}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

// Cập nhật đánh giá
Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');

//Xoa danh gia
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


Route::get('/my-favorites', [FavoriteController::class, 'index'])
    ->middleware('auth')
    ->name('favorite.hienthi'); 

Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])
    ->middleware('auth')
    ->name('favorites.toggle');

Route::delete('/favorites', [FavoriteController::class, 'clearAll'])
    ->middleware('auth')
    ->name('favorites.clearAll');


Route::fallback(function () {
    abort(404);



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
