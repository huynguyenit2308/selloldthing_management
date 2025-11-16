<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReviewService
{
    /**
     * Tạo một review mới cho sản phẩm.
     */
    public function createReview(int $productId, User $user, array $data): Review
    {
        // 1. Kiểm tra sản phẩm tồn tại
        $product = Product::findOrFail($productId);

        // 2. Tạo review
        $review = Review::create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'rating'     => $data['rating'],
            'comment'    => $data['comment'],
        ]);

        // 3. Load user
        $review->load('user');
        return $review;
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho trang chi tiết sản phẩm.
     */
    public function getProductPageData(int $productId): array
    {
        // 1. Lấy sản phẩm và các quan hệ
        $product = Product::with(['reviews.user', 'images', 'category'])
            ->findOrFail($productId);

        // 2. Tính toán
        $averageRating = round((float) $product->reviews->avg('rating'), 1);
        $reviewsCount = $product->reviews->count();

        // 3. Lấy sản phẩm tương tự
        $similarProducts = Product::with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        // 4. Trả về mảng dữ liệu
        return compact('product', 'averageRating', 'reviewsCount', 'similarProducts');
    }

    /**
     * Lấy review để chỉnh sửa (kèm check quyền).
     */
    public function getReviewForEditing(int $reviewId, User $user): Review
    {
        $review = Review::findOrFail($reviewId);

        // Kiểm tra quyền
        if ($review->user_id !== $user->id) {
            throw new AuthorizationException('Bạn không có quyền sửa đánh giá này.');
        }

        return $review;
    }

    /**
     * Cập nhật review (kèm check quyền).
     */
    public function updateReview(int $reviewId, User $user, array $data): Review
    {
        $review = Review::findOrFail($reviewId);

        // Kiểm tra quyền
        if ($review->user_id !== $user->id) {
            throw new AuthorizationException('Bạn không có quyền sửa đánh giá này.');
        }

        // Cập nhật
        $review->update([
            'rating'  => $data['rating'],
            'comment' => $data['comment'],
        ]);

        $review->load('user');
        return $review;
    }

    /**
     * Xóa review (kèm check quyền).
     */
    public function deleteReview(int $reviewId, User $user): void
    {
        $review = Review::findOrFail($reviewId);

        // Kiểm tra quyền
        if ($review->user_id !== $user->id) {
            throw new AuthorizationException('Bạn không có quyền xóa đánh giá này.');
        }

        // Xóa (Review model nên có onDelete('cascade') cho comments)
        $review->delete();
    }

    /**
     * Lấy chi tiết 1 review và các comment của nó.
     */
    public function getReviewWithComments(int $reviewId): array
    {
        $review = Review::with('user')->findOrFail($reviewId);

        // Lấy comment cha (parent_id = null) + load replies đệ quy
        $comments = $review->comments()
            ->whereNull('parent_id')
            ->with(['user', 'repliesRecursive.user'])
            ->get(); // Bạn có thể thêm ->orderBy('created_at', 'desc')

        return compact('review', 'comments');
    }
}