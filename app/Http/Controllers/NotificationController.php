<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Comment;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Lấy danh sách thông báo
     */
    public function index(Request $request)
    {
        return $this->notificationService->getNotifications(Auth::id(), $request);
    }

    /**
     * Lấy số lượng thông báo chưa đọc
     */
    public function getUnreadCount()
    {
        return $this->notificationService->getUnreadCount(Auth::id());
    }

    /**
     * Đánh dấu 1 thông báo đã đọc
     */
    public function markAsRead($id)
    {
        return $this->notificationService->markAsRead(Auth::id(), $id);
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead()
    {
        return $this->notificationService->markAllAsRead(Auth::id());
    }

    /**
     * Xóa 1 thông báo
     */
    public function destroy($id)
    {
        return $this->notificationService->delete(Auth::id(), $id);
    }

    /**
     * Xóa tất cả thông báo
     */
    public function destroyAll()
    {
        return $this->notificationService->deleteAll(Auth::id());
    }

    /**
     * Gửi thông báo khi có sản phẩm mới
     */
    public function notifyNewProduct(Product $product)
    {
        $this->notificationService->notifyWatchersOfNewProduct($product);
        return response()->json(['success' => true]);
    }
}
