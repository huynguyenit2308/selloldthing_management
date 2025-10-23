<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function createComment($reviewId, $content, $parentId = null)
    {
        return Comment::create([
            'review_id' => $reviewId,
            'user_id' => Auth::id(),
            'content' => $content,
            'parent_id' => $parentId,
        ]);
    }
    // Tạo phản hồi cho comment
    public function createReply(Comment $parentComment, $content)
    {
        return Comment::create([
            'review_id' => $parentComment->review_id,
            'user_id' => Auth::id(),
            'content' => $content,
            'parent_id' => $parentComment->id,
        ]);
    }
}
