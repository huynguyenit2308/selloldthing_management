<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product; // Giữ lại vì Validator 'exists'
use App\Models\Category; // Giữ lại
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\FavoriteService; // ✅ Thêm Service

class FavoriteController extends Controller
{
    protected $favoriteService;

    // ✅ Tiêm Service vào constructor
    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * Hiển thị trang quản lý danh sách yêu thích (dạng bảng).
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        // 1. Lấy các biến từ request
        $sortOption = $request->input('sort', 'newest');
        $user = Auth::user();

        // 2. Gọi Service để lấy toàn bộ dữ liệu
        // Service sẽ lo: query, sort, paginate, lấy categories, v.v.
        $data = $this->favoriteService->getFavoritesPageData($user, $sortOption);

        // 3. Trả về view
        return view('favorite.hienthi', $data);
    }

    /**
     * Thêm hoặc xóa một sản phẩm khỏi danh sách yêu thích của người dùng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        // 1. Xác thực (Controller)
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ.'], 400);
        }

        // 2. Lấy dữ liệu
        $user = Auth::user();
        $productId = $request->input('product_id');

        // 3. [CẬP NHẬT] Gọi Service để xử lý (Service lo Transaction)
        try {
            $status = $this->favoriteService->toggleFavorite($user, $productId);

            // 4. Trả về phản hồi JSON
            return response()->json([
                'status' => $status,
                'product_id' => $productId,
            ]);
        } catch (\Exception $e) {
            // 5. Bắt lỗi nếu Service ném ra
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }
}