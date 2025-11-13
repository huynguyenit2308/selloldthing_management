<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
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
    public function index(Request $request): View
    {
        // 1. Lấy các biến cho Sắp xếp (giống file "Sản phẩm của tôi")
        $sortLabels = [
            'newest' => 'Mới nhất (Thêm vào)', // Sửa label
            'oldest' => 'Cũ nhất (Thêm vào)', // Sửa label
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
        $query = $user->favorites() // Bắt đầu từ relationship
            ->with(['images' => function ($q) { // Lấy ảnh
                $q->orderBy('created_at');
            }])
            // Join với bảng products để có thể sort theo tên, giá, views
            // ->join('products', 'products.id', '=', 'favorites.product_id') // <--- [XÓA DÒNG NÀY]
            ->select('products.*', 'favorites.created_at as favorited_at'); // Chọn cột

        // 3. Áp dụng Sắp xếp
        switch ($sortOption) {
            case 'oldest':
                $query->orderBy('favorites.created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('products.name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('products.name', 'desc');
                break;
            case 'price_desc':
                $query->orderBy('products.price', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('products.price', 'asc');
                break;
            case 'views_desc':
                $query->orderBy('products.view_count', 'desc');
                break;
            case 'views_asc':
                $query->orderBy('products.view_count', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('favorites.created_at', 'desc');
                break;
        }

        // 4. Phân trang
        // Đổi tên biến thành $products để tương thích với template
        $products = $query->paginate(4)->withQueryString();
        // 5. Lấy danh sách ID (vẫn cần cho nút bấm)
        $userFavoriteIds = $user->favorites()->pluck('product_id')->toArray();

        // 6. Trả về view MỚI
        // (Sử dụng view 'account.favorites_manage' mà chúng ta đã tạo)
        return view('favorite.hienthi', [
            'products' => $products,
            'sortLabels' => $sortLabels,
            'sortOption' => $sortOption,
            'userFavoriteIds' => $userFavoriteIds,
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
