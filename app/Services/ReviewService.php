<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage; // [MỚI] Thêm thư viện Storage

class ReviewService
{
    /**
     * Tạo một review mới cho sản phẩm.
     */
    public function createReview(int $productId, User $user, array $data): Review
    {
        // 1. Kiểm tra sản phẩm tồn tại
        $product = Product::findOrFail($productId);

        $imagePath = null;

        // 2. [MỚI] Kiểm tra và lưu file ảnh
        // $data['image'] được truyền từ Controller (Bước 1)
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Lưu file vào 'storage/app/public/reviews'
            // $imagePath sẽ là 'reviews/ten_file_moi_ngau_nhien.jpg'
            $imagePath = $data['image']->store('reviews', 'public');
        }

        // 3. [MỚI] Tạo review với đường dẫn ảnh (nếu có)
        $review = Review::create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'rating'     => $data['rating'],
            'comment'    => $data['comment'],
            'media'      => $imagePath, // [SỬA] Đổi 'image_path' thành 'media'
        ]);

        // 4. Load user
        $review->load('user');
        
        // 5. Trả về review
        // $review sẽ tự động có 'image_url' (nhờ Bước 3)
        // mà JavaScript đang chờ.
        return $review;
    }

    /**
     * Lấy toàn bộ dữ liệu cần thiết cho trang chi tiết sản phẩm.
     */
    public function getProductPageData(int $productId): array
    {
        // 1. Lấy sản phẩm và các quan hệ
        $product = Product::with(['reviews.user', 'reviews.comments', 'images', 'category']) // [SỬA] Thêm 'reviews.comments'
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

        // [MỚI] Tạm thời chưa xử lý update ảnh, vì JS chưa gửi lên
        // (Nếu bạn muốn update cả ảnh ở đây, logic sẽ phức tạp hơn)

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

        // [MỚI] Xóa file ảnh cũ khỏi storage nếu có
        if ($review->media) { // [SỬA] Đổi 'image_path' thành 'media'
            Storage::disk('public')->delete($review->media); // [SỬA] Đổi 'image_path' thành 'media'
        }

        // [MỚI] Tương tự, xóa ảnh của tất cả comment con (nếu có)
        // (Điều này yêu cầu bạn phải làm tương tự cho Model Comment)
        foreach ($review->comments as $comment) {
             if ($comment->media) { // [SỬA] Giả sử Comment cũng dùng 'media'
                 Storage::disk('public')->delete($comment->media); // [SỬA] Giả sử Comment cũng dùng 'media'
             }
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