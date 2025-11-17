<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // [MỚI] Thêm thư viện Storage

class Review extends Model
{
    use HasFactory;

    // [GIỮ NGUYÊN] Dùng 'media' như bạn cung cấp
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment', 'media', 'status'];

    /**
     * [MỚI] Thêm mảng 'appends' để 'image_url' luôn được
     * tự động thêm vào khi model được chuyển thành JSON.
     * Đây chính là cách JS nhận được 'image_url'.
     */
    protected $appends = ['image_url'];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->with(['user', 'repliesRecursive.user']);
    }

    /**
     * [MỚI] Accessor để tạo URL đầy đủ cho ảnh
     *
     * Tự động tạo thuộc tính 'image_url' từ cột 'media'.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        // 'media' là tên cột trong DB (ví dụ: 'reviews/file.jpg')
        if ($this->media) {
            // Storage::url() sẽ tạo URL đầy đủ
            // (ví dụ: 'https://site.com/storage/reviews/file.jpg')
            return Storage::url($this->media);
        }

        // Nếu không có ảnh, trả về null
        return null;
    }
}