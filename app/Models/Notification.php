<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope để lấy thông báo chưa đọc
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope để lấy thông báo đã đọc
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Đánh dấu thông báo là đã đọc
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Đánh dấu nhiều thông báo là đã đọc
     */
    public static function markMultipleAsRead(array $ids, $userId)
    {
        return static::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Tạo thông báo sản phẩm mới
     */
    public static function createNewProductNotification($userId, $product, $category)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'new_product',
            'title' => 'Sản phẩm mới trong danh mục theo dõi',
            'message' => "Sản phẩm mới \"{$product->name}\" vừa được thêm vào danh mục \"{$category->name}\"",
            'data' => [
                'product_id' => $product->id,
                'category_id' => $category->id,
                'product_name' => $product->name,
                'category_name' => $category->name,
                'product_price' => $product->price,
            ],
            'link' => route('products.show', $product->id),
        ]);
    }

    /**
     * Lấy số lượng thông báo chưa đọc của user
     */
    public static function getUnreadCount($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Xóa thông báo cũ (quá 30 ngày)
     */
    public static function cleanupOldNotifications()
    {
        return static::where('created_at', '<', now()->subDays(30))
            ->where('is_read', true)
            ->delete();
    }
}
