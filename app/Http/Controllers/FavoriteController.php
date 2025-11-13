<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product; 


class FavoriteController extends Controller
{
    /**
     * Thêm hoặc xóa một sản phẩm khỏi danh sách yêu thích của người dùng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        // 1. Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ.'], 400);
        }

        // 2. Lấy người dùng đã đăng nhập
        $user = Auth::user();

        // 3. Lấy product_id
        $productId = $request->input('product_id');


        $result = $user->favorites()->toggle($productId);

        // 5. Xác định trạng thái mới để trả về cho JavaScript
        $status = 'removed';
        if (count($result['attached']) > 0) {
            $status = 'added'; // Nếu có 'attached' nghĩa là vừa thêm mới
        }

        // 6. Trả về phản hồi JSON
        return response()->json([
            'status' => $status,
            'product_id' => $productId,
        ]);
    }
    // Ví dụ trong app/Http/Controllers/ProductController.php

   
}
