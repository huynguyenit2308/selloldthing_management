<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryWatchlist extends Model
{
    use HasFactory;

    protected $table = 'category_watchlist';

    protected $fillable = [
        'user_id',
        'category_id',
    ];

    /**
     * Giới hạn số lượng danh mục có thể theo dõi
     */
    const MAX_WATCHLIST_ITEMS = 50;

    /**
     * Quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Kiểm tra xem user đã theo dõi category này chưa
     */
    public static function isWatching($userId, $categoryId)
    {
        return static::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->exists();
    }

    /**
     * Đếm số lượng danh mục đang theo dõi của user
     */
    public static function countUserWatchlist($userId)
    {
        return static::where('user_id', $userId)->count();
    }

    /**
     * Kiểm tra xem có thể thêm danh mục vào watchlist không
     */
    public static function canAddToWatchlist($userId)
    {
        return static::countUserWatchlist($userId) < static::MAX_WATCHLIST_ITEMS;
    }
}
