<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryWatchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CategoryWatchlistController extends Controller
{
    /**
     * Hiển thị danh sách danh mục đang theo dõi
     */
    public function index(Request $request)
    {
        try {
            // Kiểm tra authentication
            if (!Auth::check()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'UNAUTHORIZED',
                        'message' => 'Vui lòng đăng nhập để xem danh mục theo dõi'
                    ], 401);
                }
                return redirect()->route('login.form')
                    ->with('error', 'Vui lòng đăng nhập để xem danh mục theo dõi')
                    ->with('return_url', route('watchlist.index'));
            }

            $user = Auth::user();
            
            // Rate limiting - tránh spam request
            $key = 'watchlist-load:' . $user->id;
            if (RateLimiter::tooManyAttempts($key, 60)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau 1 phút.'
                    ], 429);
                }
                return back()->with('error', 'Quá nhiều yêu cầu. Vui lòng thử lại sau 1 phút.');
            }
            RateLimiter::hit($key, 60);

            // Lấy từ khóa tìm kiếm
            $search = $request->input('search', '');

            // Lấy danh sách danh mục theo dõi với thông tin chi tiết
            $watchlistQuery = CategoryWatchlist::where('user_id', $user->id)
                ->with(['category' => function ($query) {
                    $query->withCount('products');
                }])
                ->orderBy('created_at', 'desc');

            // Áp dụng tìm kiếm nếu có
            if (!empty($search)) {
                // Validate search query
                if (strlen($search) > 100) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'INVALID_SEARCH_QUERY',
                            'message' => 'Từ khóa tìm kiếm không hợp lệ'
                        ], 400);
                    }
                    return back()->with('error', 'Từ khóa tìm kiếm quá dài');
                }

                $watchlistQuery->whereHas('category', function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%');
                });
            }

            $watchlist = $watchlistQuery->paginate(12);

            // Xử lý empty state
            if ($watchlist->isEmpty() && empty($search)) {
                // Danh sách trống - không có lỗi, chỉ empty state
                if ($request->expectsJson()) {
                    return response()->json([
                        'data' => [],
                        'message' => 'Bạn chưa theo dõi danh mục nào'
                    ], 200);
                }
            }

            // Lấy tất cả danh mục để hiển thị suggestions
            $allCategories = Category::withCount('products')
                ->orderBy('name')
                ->get();

            return view('watchlist.index', [
                'watchlist' => $watchlist,
                'allCategories' => $allCategories,
                'search' => $search,
                'maxItems' => CategoryWatchlist::MAX_WATCHLIST_ITEMS,
                'currentCount' => CategoryWatchlist::countUserWatchlist($user->id)
            ]);

        } catch (\Exception $e) {
            // Log lỗi server
            Log::error('Watchlist load error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'NETWORK_ERROR',
                    'message' => 'Không thể tải danh sách theo dõi'
                ], 500);
            }

            return back()->with('error', 'Không thể tải danh sách theo dõi. Vui lòng thử lại sau.');
        }
    }

    /**
     * Thêm danh mục vào watchlist
     */
    public function store(Request $request, $categoryId)
    {
        try {
            // Kiểm tra authentication
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Vui lòng đăng nhập để theo dõi danh mục'
                ], 401);
            }

            $user = Auth::user();

            // Rate limiting
            $key = 'watchlist-add:' . $user->id;
            if (RateLimiter::tooManyAttempts($key, 20)) {
                return response()->json([
                    'error' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau 1 phút.'
                ], 429);
            }
            RateLimiter::hit($key, 60);

            // Kiểm tra category có tồn tại không
            $category = Category::find($categoryId);
            if (!$category) {
                return response()->json([
                    'error' => 'CATEGORY_NOT_FOUND',
                    'message' => 'Danh mục không tồn tại'
                ], 404);
            }

            // Kiểm tra giới hạn số lượng
            if (!CategoryWatchlist::canAddToWatchlist($user->id)) {
                return response()->json([
                    'error' => 'WATCHLIST_LIMIT_REACHED',
                    'message' => 'Bạn chỉ có thể theo dõi tối đa ' . CategoryWatchlist::MAX_WATCHLIST_ITEMS . ' danh mục'
                ], 400);
            }

            // Kiểm tra đã theo dõi chưa
            if (CategoryWatchlist::isWatching($user->id, $categoryId)) {
                return response()->json([
                    'error' => 'ALREADY_IN_WATCHLIST',
                    'message' => 'Bạn đã theo dõi danh mục này rồi'
                ], 400);
            }

            // Thêm vào watchlist
            DB::beginTransaction();
            try {
                CategoryWatchlist::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId
                ]);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm vào danh sách theo dõi',
                    'data' => [
                        'category_id' => $categoryId,
                        'category_name' => $category->name
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Watchlist add error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'category_id' => $categoryId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'NETWORK_ERROR',
                'message' => 'Không thể thêm vào danh sách theo dõi'
            ], 500);
        }
    }

    /**
     * Xóa danh mục khỏi watchlist
     */
    public function destroy(Request $request, $categoryId)
    {
        try {
            // Kiểm tra authentication
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Vui lòng đăng nhập'
                ], 401);
            }

            $user = Auth::user();

            // Rate limiting
            $key = 'watchlist-remove:' . $user->id;
            if (RateLimiter::tooManyAttempts($key, 30)) {
                return response()->json([
                    'error' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.'
                ], 429);
            }
            RateLimiter::hit($key, 60);

            // Tìm watchlist item
            $watchlistItem = CategoryWatchlist::where('user_id', $user->id)
                ->where('category_id', $categoryId)
                ->first();

            if (!$watchlistItem) {
                // Kiểm tra xem category có tồn tại không
                $category = Category::find($categoryId);
                if (!$category) {
                    return response()->json([
                        'error' => 'CATEGORY_NOT_FOUND',
                        'message' => 'Danh mục không tồn tại',
                        'auto_remove' => true
                    ], 404);
                }

                return response()->json([
                    'error' => 'NOT_IN_WATCHLIST',
                    'message' => 'Danh mục không có trong danh sách theo dõi'
                ], 400);
            }

            // Xóa khỏi watchlist
            $watchlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã bỏ theo dõi danh mục'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Watchlist remove error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'category_id' => $categoryId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'NETWORK_ERROR',
                'message' => 'Không thể bỏ theo dõi danh mục'
            ], 500);
        }
    }

    /**
     * Toggle watchlist (thêm hoặc xóa)
     */
    public function toggle(Request $request, $categoryId)
    {
        if (CategoryWatchlist::isWatching(Auth::id(), $categoryId)) {
            return $this->destroy($request, $categoryId);
        } else {
            return $this->store($request, $categoryId);
        }
    }
}
