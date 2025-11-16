<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewService; // ✅ Thêm service
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReviewController extends Controller
{
    protected $reviewService;

    // ✅ Tiêm service vào constructor
    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    // ⛔️ Phương thức này có vẻ không cần thiết
    // public function create($id)
    // {
    //     $product = Product::findOrFail($id);
    //     return view('product.show', compact('product'));
    // }

    // 🟦 LƯU REVIEW MỚI
    public function store(Request $request, $id)
    {
        // 1. Validate (Controller)
        $validatedData = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        try {
            // 2. Gọi Service (Service)
            $review = $this->reviewService->createReview(
                $id, // product id
                auth()->user(),
                $validatedData
            );

            // 3. Trả về JSON (Controller)
            return response()->json([
                'success' => true,
                'review' => $review,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }
    }

    // 🟩 HIỂN THỊ TRANG CHI TIẾT SẢN PHẨM (Bao gồm reviews)
    public function showReviews($id)
    {
        // 1. Gọi Service để lấy toàn bộ dữ liệu trang (Service)
        // Service sẽ ném 404 nếu không tìm thấy $id
        $data = $this->reviewService->getProductPageData($id);

        // 2. Trả về view (Controller)
        return view('product.show', $data);
    }

    // 🟧 LẤY DATA REVIEW ĐỂ SỬA
    public function edit($id)
    {
        try {
            // 1. Gọi Service (Service tự check quyền)
            $review = $this->reviewService->getReviewForEditing($id, auth()->user());

            // 2. Trả về JSON (Controller)
            return response()->json([
                'success' => true,
                'review' => $review,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Review không tồn tại.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    // 🟨 CẬP NHẬT REVIEW
    public function update(Request $request, $id)
    {
        // 1. Validate (Controller)
        $validatedData = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        try {
            // 2. Gọi Service (Service tự check quyền)
            $review = $this->reviewService->updateReview(
                $id,
                auth()->user(),
                $validatedData
            );

            // 3. Trả về JSON (Controller)
            return response()->json([
                'success' => true,
                'review' => $review,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Review không tồn tại.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    // 🟥 XÓA REVIEW
    public function destroy($id)
    {
        try {
            // 1. Gọi Service (Service tự check quyền)
            $this->reviewService->deleteReview($id, auth()->user());

            // 2. Trả về JSON (Controller)
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa đánh giá thành công.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Review không tồn tại.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    // 🟪 HIỂN THỊ CHI TIẾT 1 REVIEW (VÀ COMMENT CỦA NÓ)
    public function show($id)
    {
        try {
            // 1. Gọi Service (Service)
            $data = $this->reviewService->getReviewWithComments($id);

            // 2. Trả về view (Controller)
            return view('product.show', $data); // Giả sử bạn dùng chung 1 view
            // Hoặc: return view('review.detail', $data);
        } catch (ModelNotFoundException $e) {
            abort(404, 'Không tìm thấy đánh giá này.');
        }
    }
}