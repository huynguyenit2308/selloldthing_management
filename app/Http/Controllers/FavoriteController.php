<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse; // ✅ Thêm RedirectResponse
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product; // Giữ lại vì Validator 'exists'
use App\Models\Category; // Giữ lại
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\FavoriteService; // ✅ Thêm Service
use Illuminate\Validation\ValidationException; // ✅ Thêm ValidationException

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
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Validate form parameters for favorites list
        try {
            $this->validateFavoriteFilters($request);
        } catch (ValidationException $e) {
            // Clear invalid filter parameters when redirecting
            $input = $request->except(['sort', 'filter_type', 'category', 'search']);
            return redirect()
                ->route('favorite.hienthi')
                ->withInput($input)
                ->withErrors($e->errors());
        }

        // 1. Lấy các biến từ request
        $sortOption = $request->input('sort', 'newest');
        $filterType = $request->input('filter_type');
        $categoryId = $request->filled('category') ? (int) $request->input('category') : null;
        $user = Auth::user();

        // 2. Gọi Service để lấy toàn bộ dữ liệu
        // Service sẽ lo: query, sort, paginate, lấy categories, v.v.
        $data = $this->favoriteService->getFavoritesPageData($user, $sortOption, $filterType, $categoryId);

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

    public function clearAll(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        DB::table('favorites')->where('user_id', $user->id)->delete();

        return response()->json([
            'status' => 'cleared',
        ]);
    }

    /**
     * Validate favorite list filters
     * Validate bộ lọc danh sách yêu thích
     */
    protected function validateFavoriteFilters(Request $request): void
    {
        // Validate search term
        $searchTerm = $request->input('search');
        if ($searchTerm !== null && $searchTerm !== '') {
            $trimmedTerm = trim($searchTerm);
            
            // Check if search term is too short or too long
            if (strlen($trimmedTerm) < 2) {
                throw ValidationException::withMessages([
                    'search' => 'Từ khóa tìm kiếm phải có ít nhất 2 ký tự',
                ]);
            }
            
            if (strlen($trimmedTerm) > 100) {
                throw ValidationException::withMessages([
                    'search' => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự',
                ]);
            }

            // Check for potentially dangerous patterns
            $dangerousPatterns = ['<script', 'javascript:', 'data:', 'vbscript:'];
            foreach ($dangerousPatterns as $pattern) {
                if (stripos($trimmedTerm, $pattern) !== false) {
                    throw ValidationException::withMessages([
                        'search' => 'Từ khóa tìm kiếm chứa ký tự không hợp lệ',
                    ]);
                }
            }
        }

        // Validate sort option
        $sort = $request->input('sort', 'newest');
        $validSorts = ['newest', 'oldest', 'name_asc', 'name_desc', 'price_asc', 'price_desc'];
        
        if (!in_array($sort, $validSorts, true)) {
            throw ValidationException::withMessages([
                'sort' => 'Tiêu chí sắp xếp không hợp lệ cho danh sách yêu thích',
            ]);
        }

        // Validate filter type
        $filterType = $request->input('filter_type');
        if ($filterType !== null && $filterType !== '') {
            $validFilterTypes = ['all', 'available', 'sold', 'discounted'];
            
            if (!in_array($filterType, $validFilterTypes, true)) {
                throw ValidationException::withMessages([
                    'filter_type' => 'Loại bộ lọc không hợp lệ',
                ]);
            }
        }

        // Validate category
        $categoryId = $request->input('category');
        if ($categoryId !== null && $categoryId !== '') {
            if (!is_numeric($categoryId) || (int)$categoryId < 1) {
                throw ValidationException::withMessages([
                    'category' => 'Danh mục không hợp lệ',
                ]);
            }

            if (!Category::where('id', $categoryId)->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Danh mục không tồn tại',
                ]);
            }
        }
    }
}