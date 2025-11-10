<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\CategoryWatchlist;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Gửi thông báo cho user đang theo dõi danh mục khi có sản phẩm mới
     */
    public function notifyWatchersOfNewProduct(Product $product)
    {
        try {
            // Lấy category của sản phẩm
            $category = $product->category;
            
            if (!$category) {
                Log::warning('Không tìm thấy danh mục cho sản phẩm', ['product_id' => $product->id]);
                return;
            }

            // Lấy danh sách user đang theo dõi danh mục này (trừ người tạo sản phẩm)
            $watchers = CategoryWatchlist::where('category_id', $category->id)
                ->where('user_id', '!=', $product->user_id)
                ->pluck('user_id');

            if ($watchers->isEmpty()) {
                Log::info('Không có user nào theo dõi danh mục', ['category_id' => $category->id]);
                return;
            }

            // Tạo thông báo cho từng user
            $notificationsCreated = 0;
            foreach ($watchers as $userId) {
                Notification::createNewProductNotification($userId, $product, $category);
                $notificationsCreated++;
            }

            Log::info('Đã tạo thông báo sản phẩm mới', [
                'product_id' => $product->id,
                'category_id' => $category->id,
                'notifications_count' => $notificationsCreated
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo thông báo sản phẩm mới', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Đánh dấu tất cả thông báo của user là đã đọc
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

            Log::info('Đã đánh dấu tất cả thông báo là đã đọc', ['user_id' => $userId]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi khi đánh dấu thông báo đã đọc', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Xóa thông báo cũ (chạy định kỳ)
     */
    public function cleanupOldNotifications($days = 30)
    {
        try {
            $deleted = Notification::where('created_at', '<', now()->subDays($days))
                ->where('is_read', true)
                ->delete();

            Log::info('Đã xóa thông báo cũ', ['deleted_count' => $deleted]);
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa thông báo cũ', ['error' => $e->getMessage()]);
            
            return 0;
        }
    }
}
