<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Review;
use App\Models\User; // Hoặc model User của bạn
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommentService
{
    /**
     * Tạo một bình luận mới cho Review.
     *
     * @param int $reviewId
     * @param string $content
     * @param int|null $parentId
     * @return Comment
     * @throws ModelNotFoundException
     */
    public function createComment(int $reviewId, string $content, ?int $parentId = null): Comment
    {
        // 1. Kiểm tra Review cha có tồn tại không
        $review = Review::find($reviewId);

        if (!$review) {
            throw new ModelNotFoundException('Đánh giá (review) bạn muốn bình luận không còn tồn tại.');
        }

        // 2. Tạo comment
        $comment = Comment::create([
            'review_id' => $review->id,
            'user_id'   => Auth::id(),
            'content'   => $content,
            'parent_id' => $parentId,
        ]);

        // 3. Load thông tin user để trả về controller
        $comment->load('user');

        return $comment;
    }

    /**
     * Tạo một phản hồi (reply) cho một Comment.
     *
     * @param Comment $parentComment
     * @param string $content
     * @return Comment
     */
    public function createReply(Comment $parentComment, string $content): Comment
    {
        // 1. Tạo reply
        $reply = Comment::create([
            'review_id' => $parentComment->review_id,
            'user_id'   => Auth::id(),
            'content'   => $content,
            'parent_id' => $parentComment->id,
        ]);

        // 2. Load thông tin user
        $reply->load('user');

        return $reply;
    }

    /**
     * Cập nhật nội dung một Comment.
     *
     * @param Comment $comment
     * @param string $content
     * @param User $user (Người dùng đang đăng nhập)
     * @return Comment
     * @throws AuthorizationException
     */
    public function updateComment(Comment $comment, string $content, User $user): Comment
    {
        // 1. Kiểm tra quyền
        if ($user->id !== $comment->user_id) {
            throw new AuthorizationException('Không có quyền chỉnh sửa bình luận này.');
        }

        // 2. Cập nhật
        $comment->update([
            'content' => $content
        ]);

        return $comment;
    }

    /**
     * Xóa một Comment.
     *
     * @param Comment $comment
     * @param User $user (Người dùng đang đăng nhập)
     * @return void
     * @throws AuthorizationException
     */
    public function deleteComment(Comment $comment, User $user): void
    {
        // 1. Kiểm tra quyền
        if ($user->id !== $comment->user_id) {
            throw new AuthorizationException('Không có quyền xóa bình luận này.');
        }

        // 2. Xóa
        // Model Comment sẽ tự động xóa các 'replies' con nếu bạn đã
        // thiết lập 'onDelete('cascade')' trong migration.
        $comment->delete();
    }
}
