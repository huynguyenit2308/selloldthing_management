<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['review_id', 'user_id', 'content', 'parent_id'];

    // 🔹 Quan hệ đến bài đánh giá (Review)
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    // 🔹 Quan hệ đến người dùng
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Các phản hồi (comment con trực tiếp)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user');
    }

    // 🔹 Quan hệ đệ quy — để lấy *mọi cấp con cháu* của bình luận
    public function repliesRecursive()
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->with(['user', 'repliesRecursive']);
    }
}
