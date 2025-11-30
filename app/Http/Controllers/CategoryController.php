<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    // Admin: Quản lý danh mục
    public function index(Request $request)
    {
        // [TEST CASE] Validate page parameter
        $page = $request->query('page');
        if ($page !== null && (!is_numeric($page) || $page <= 0 || $page > 999999)) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'PAGE_INVALID: Số trang không hợp lệ']);
        }

        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status'); // active|inactive|null
        
        // Validate query parameter - nếu quá dài thì báo lỗi
        if (strlen($q) > 100) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['q' => 'CATEGORY_QUERY_TOO_LONG: Từ khóa tìm kiếm không được vượt quá 100 ký tự']);
        }
        
        // Validate status parameter - nếu có giá trị nhưng không hợp lệ thì báo lỗi
        if ($status !== null && $status !== '' && !in_array($status, ['active', 'inactive'])) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_NOT_FOUND: Danh mục không tồn tại']);
        }
        
        $categoriesQuery = Category::query()
            ->select(['id', 'name', 'description', 'image', 'status', 'created_at']);

        if ($q !== '') {
            $categoriesQuery->where('name', 'like', "%{$q}%");
        }

        $statusMap = [
            'active' => 1,
            'inactive' => 0,
        ];

        if (array_key_exists($status, $statusMap)) {
            $categoriesQuery->where('status', $statusMap[$status]);
        }

        $categories = $categoriesQuery->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Validate page - nếu page vượt quá số trang thực tế
        $page = $request->query('page');
        if ($page !== null && is_numeric($page) && $page > $categories->lastPage()) {
            return redirect()
                ->route('admin.categories.index')
                ->withInput()
                ->withErrors(['page' => 'Trang bạn yêu cầu không tồn tại. Chỉ có ' . $categories->lastPage() . ' trang.']);
        }

        return view('admin.categories.index', [
            'categories' => $categories,
            'q' => $q,
            'status' => $status,
        ]);
    }

    // Frontend: Hiển thị tất cả danh mục
    public function indexFrontend(): View
    {
        try {
            $categories = Category::where('status', 1)
                ->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('name')
                ->get();

            // Lấy danh sách ID của các danh mục user đã theo dõi
            $watchedCategoryIds = [];
            if (auth()->check()) {
                $watchedCategoryIds = \App\Models\CategoryWatchlist::where('user_id', auth()->id())
                    ->pluck('category_id')
                    ->toArray();
            }

            return view('admin.categories.index_category', [
                'categories' => $categories,
                'watchedCategoryIds' => $watchedCategoryIds,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('CATEGORY_LIST_ERROR: ' . $e->getMessage());

            return view('admin.categories.index_category', [
                'categories' => collect([]),
                'watchedCategoryIds' => [],
                'error' => [
                    'type' => 'CONNECTION_ERROR',
                    'message' => 'Không thể tải danh sách danh mục',
                    'action' => 'retry'
                ],
            ]);
        }
    }

    public function show(Category $category, Request $request)
    {
        // [TEST CASE] Validate page parameter
        $page = $request->query('page');
        if ($page !== null && (!is_numeric($page) || $page <= 0 || $page > 999999)) {
            return redirect()
                ->route('categories.show', $category->id)
                ->withErrors(['system' => 'PAGE_INVALID: Số trang không hợp lệ']);
        }

        try {
            // Kiểm tra danh mục có active không
            if ($category->status != 1) {
                return view('admin.categories.show_category', [
                    'category' => $category,
                    'products' => collect([]),
                    'allCategories' => collect([]),
                    'sort' => 'newest',
                    'filter' => 'all',
                    'error' => [
                        'type' => 'CATEGORY_INACTIVE',
                        'message' => 'Danh mục này hiện không khả dụng',
                        'action' => 'redirect'
                    ],
                ]);
            }

            // Lấy tất cả danh mục để hiển thị sidebar
            $allCategories = Category::where('status', 1)->orderBy('name')->get();

            // Lấy các tham số lọc và sắp xếp từ request
            $sort = $request->query('sort', 'newest');
            $filter = $request->query('filter', 'all');
            $page = $request->query('page');

            // Validate tham số sort
            $validSorts = ['newest', 'oldest', 'price_asc', 'price_desc'];
            if ($sort !== null && !in_array($sort, $validSorts)) {
                return redirect()
                    ->route('categories.show', $category->id)
                    ->withInput()
                    ->withErrors(['sort' => 'Bạn chọn sắp xếp không phù hợp']);
            }

            // Query sản phẩm theo danh mục
            $productsQuery = Product::published()
                ->where('category_id', $category->id)
                ->with([
                    'images' => function ($query) {
                        $query->orderBy('sort_order')->orderBy('created_at');
                    },
                    'category',
                ]);

            // Áp dụng bộ lọc
            if ($filter === 'discount') {
                $productsQuery->whereColumn('original_price', '>', 'price')
                    ->whereNotNull('original_price');
            }

            // Áp dụng sắp xếp
            switch ($sort) {
                case 'oldest':
                    $productsQuery->oldest('created_at');
                    break;
                case 'price_asc':
                    $productsQuery->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $productsQuery->orderBy('price', 'desc');
                    break;
                default:
                    $productsQuery->latest('created_at');
                    break;
            }

            // Phân trang sản phẩm (6 sản phẩm mỗi trang)
            $products = $productsQuery->paginate(6)->withQueryString();

            // Validate page - nếu page vượt quá số trang thực tế
            if ($page !== null && is_numeric($page) && $page > $products->lastPage()) {
                return redirect()
                    ->route('categories.show', $category->id)
                    ->withInput()
                    ->withErrors(['page' => 'Trang bạn yêu cầu không tồn tại. Chỉ có ' . $products->lastPage() . ' trang.']);
            }

            return view('admin.categories.show_category', [
                'category' => $category,
                'products' => $products,
                'allCategories' => $allCategories,
                'sort' => $sort,
                'filter' => $filter,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('CATEGORY_SHOW_ERROR: ' . $e->getMessage());

            return view('admin.categories.show_category', [
                'category' => $category ?? null,
                'products' => collect([]),
                'allCategories' => collect([]),
                'sort' => 'newest',
                'filter' => 'all',
                'error' => [
                    'type' => 'CONNECTION_ERROR',
                    'message' => 'Không thể tải danh sách sản phẩm',
                    'action' => 'retry'
                ],
            ]);
        }
    }

    public function destroy(Category $category)
    {
        try {
            // Kiểm tra quyền admin
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'error_code' => 'PERMISSION_DENIED',
                    'message' => 'Bạn không có quyền admin'
                ], 403);
            }

            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công'
            ]);

        } catch (Exception $e) {
            Log::error('CATEGORY_DELETE_FAILED: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error_code' => 'DELETE_FAILED',
                'message' => 'Xóa không hợp lệ'
            ], 500);
        }
    }
}
