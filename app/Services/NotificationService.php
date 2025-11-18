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
     * Gửi thông báo cho user khi sản phẩm được duyệt
     */
    public function notifyProductApproval(Product $product)
    {
        try {
            // Tạo thông báo cho người đăng sản phẩm
            Notification::create([
                'user_id' => $product->user_id,
                'type' => 'product_approved',
                'title' => 'Sản phẩm đã được duyệt',
                'message' => "Sản phẩm \"{$product->name}\" của bạn đã được duyệt và hiển thị trên trang chủ.",
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                ],
                'link' => route('products.show', $product->id),
            ]);

            Log::info('Đã tạo thông báo duyệt sản phẩm', [
                'product_id' => $product->id,
                'user_id' => $product->user_id
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo thông báo duyệt sản phẩm', [
                'product_id' => $product->id,
                'user_id' => $product->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Gửi thông báo cho user khi sản phẩm bị từ chối
     */
    public function notifyProductRejection(Product $product, $reason)
    {
        try {
            // Tạo thông báo cho người đăng sản phẩm
            Notification::create([
                'user_id' => $product->user_id,
                'type' => 'product_rejected',
                'title' => 'Sản phẩm bị từ chối',
                'message' => "Sản phẩm \"{$product->name}\" của bạn đã bị từ chối. Lý do: {$reason}",
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'rejection_reason' => $reason,
                ],
                'link' => route('products.manage'),
            ]);

            Log::info('Đã tạo thông báo từ chối sản phẩm', [
                'product_id' => $product->id,
                'user_id' => $product->user_id,
                'reason' => $reason
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo thông báo từ chối sản phẩm', [
                'product_id' => $product->id,
                'user_id' => $product->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
