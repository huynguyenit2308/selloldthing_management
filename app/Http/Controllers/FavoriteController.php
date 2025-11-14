<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB; // <-- Thêm thư viện DB
use Illuminate\View\View; // <-- Thêm thư viện View

class FavoriteController extends Controller
{
    /**
     * [ĐÂY LÀ HÀM MỚI]
     * Hiển thị trang quản lý danh sách yêu thích (dạng bảng).
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View // Tên hàm có thể là 'hienthi'
    {
        // 1. Lấy các biến cho Sắp xếp
        $sortLabels = [
            'newest' => 'Mới nhất (Thêm vào)',
            'oldest' => 'Cũ nhất (Thêm vào)',
            'name_asc' => 'A-Z',
            'name_desc' => 'Z-A',
            'price_desc' => 'Giá cao nhất',
            'price_asc' => 'Giá thấp nhất',
            'views_desc' => 'Lượt xem nhiều nhất',
            'views_asc' => 'Lượt xem ít nhất',
        ];
        $sortOption = $request->input('sort', 'newest');

        // 2. Lấy người dùng và query cơ sở
        $user = Auth::user();
        $query = $user->favorites() // Bắt đầu từ relationship belongsToMany
            ->with(['images' => function ($q) {
                $q->orderBy('created_at');
            }]);

        // 3. Áp dụng Sắp xếp
        switch ($sortOption) {
            case 'oldest':
                $query->orderBy('favorites.created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('products.name', 'asc');
                break;
            // ... (các case sort khác) ...
            default:
                $query->orderBy('favorites.created_at', 'desc');
                break;
        }

        // 4. Phân trang
        $products = $query->paginate(12)->withQueryString(); // Tăng số lượng lên 12 cho đẹp

        // 5. Lấy danh sách ID 
        $userFavoriteIds = $user->favorites()->pluck('products.id')->toArray();


        // 6. [THÊM DÒNG NÀY ĐỂ SỬA LỖI]
        $categories = Category::where('status', 1)->orderBy('name')->get();


        // 7. Trả về view
        return view('favorite.hienthi', [ // Đảm bảo tên view là đúng
            'products' => $products,
            'sortLabels' => $sortLabels,
            'sortOption' => $sortOption,
            'userFavoriteIds' => $userFavoriteIds,
            'categories' => $categories // <-- Giờ biến này đã tồn tại!
        ]);
    }

    /**
     * [HÀM CŨ CỦA BẠN - ĐÃ CẬP NHẬT]
     * Thêm hoặc xóa một sản phẩm khỏi danh sách yêu thích của người dùng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        // 1. Xác thực
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ.'], 400);
        }

        // 2. Lấy người dùng
        $user = Auth::user();

        // 3. Lấy product_id
        $productId = $request->input('product_id');

        // 4. [CẬP NHẬT] Thêm DB Transaction
        DB::beginTransaction();
        try {
            // Sử dụng hàm toggle()
            $result = $user->favorites()->toggle($productId);

            // Xác định trạng thái
            $status = 'removed';
            if (count($result['attached']) > 0) {
                $status = 'added';
            }

            // Commit
            DB::commit();

            // 6. Trả về phản hồi JSON
            return response()->json([
                'status' => $status,
                'product_id' => $productId,
            ]);
        } catch (\Exception $e) {
            // Nếu có lỗi, rollback
            DB::rollBack();
            return response()->json(['error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }
}
