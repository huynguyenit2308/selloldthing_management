<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\CategoryWatchlist;
use App\Models\Product;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NotificationService
{
    /**
     * ============================
     * 1. Gửi thông báo khi có SP mới
     * ============================
     */
    public function notifyWatchersOfNewProduct(Product $product)
    {
        try {
            $category = $product->category;

            if (!$category) {
                Log::warning('Không tìm thấy danh mục cho sản phẩm', ['product_id' => $product->id]);
                return;
            }

            $watchers = CategoryWatchlist::where('category_id', $category->id)
                ->where('user_id', '!=', $product->user_id)
                ->pluck('user_id');

            if ($watchers->isEmpty()) {
                Log::info('Không có user theo dõi danh mục', ['category_id' => $category->id]);
                return;
            }

            $count = 0;
            foreach ($watchers as $userId) {
                Notification::createNewProductNotification($userId, $product, $category);
                $count++;
            }

            Log::info("Đã tạo thông báo sản phẩm mới", [
                'product_id' => $product->id,
                'category_id' => $category->id,
                'notifications' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tạo thông báo sản phẩm mới', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * 3. Lấy danh sách thông báo
     * ============================
     */
    public function getNotifications($userId, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $notifications = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'unread_count' => Notification::getUnreadCount($userId),
                'total' => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi lấy thông báo", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * ============================
     * 4. Lấy số chưa đọc
     * ============================
     */
    public function getUnreadCount($userId)
    {
        try {
            return response()->json([
                'success' => true,
                'count' => Notification::getUnreadCount($userId),
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi lấy unread count", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * ============================
     * 5. Đánh dấu 1 thông báo đã đọc
     * ============================
     */
    public function markAsRead($userId, $notificationId)
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu là đã đọc'
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi markAsRead", [
                'id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * ============================
     * 6. Đánh dấu tất cả thông báo đã đọc
     * ============================
     */
    public function markAllAsRead($userId)
    {
        try {
            Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả là đã đọc'
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi markAllAsRead", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * ============================
     * 7. Xóa 1 thông báo
     * ============================
     */
    public function delete($userId, $notificationId)
    {
        try {
            Notification::where('id', $notificationId)
                ->where('user_id', $userId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thông báo'
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi delete notification", [
                'id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * ============================
     * 8. Xóa tất cả thông báo
     * ============================
     */
    public function deleteAll($userId)
    {
        try {
            Notification::where('user_id', $userId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tất cả thông báo'
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi deleteAll", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * ============================
     * 9. Xóa thông báo cũ (cronjob)
     * ============================
     */
    public function cleanupOldNotifications($days = 30)
    {
        try {
            $deleted = Notification::where('created_at', '<', now()->subDays($days))
                ->where('is_read', true)
                ->delete();

            Log::info('Đã xóa thông báo cũ', ['deleted' => $deleted]);

            return $deleted;

        } catch (\Exception $e) {
            Log::error("Lỗi cleanupOldNotifications", [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }
}
