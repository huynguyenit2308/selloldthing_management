<?php

namespace App\Services;

use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr; // Hỗ trợ lấy mảng

class FavoriteService
{
    /**
     * Lấy toàn bộ dữ liệu cần thiết cho trang Yêu thích.
     *
     * @param User $user
     * @param string $sortOption
     * @return array
     */
    public function getFavoritesPageData(User $user, string $sortOption, ?string $filterType = null, ?int $categoryId = null): array
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

        // 2. Query cơ sở
        $query = $user->favorites()
            ->with(['images' => function ($q) {
                $q->orderBy('created_at');
            }]);

        // 2.1. Lọc theo danh mục (nếu có)
        if ($categoryId !== null) {
            $query->where('products.category_id', $categoryId);
        }

        // 2.2. Lọc theo kiểu filter (nếu có)
        if ($filterType === 'on_sale') {
            $query->whereNotNull('products.original_price')
                ->whereColumn('products.original_price', '>', 'products.price');
        }

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
            default:
                $query->orderBy('favorites.created_at', 'desc');
                break;
        }

        // 4. Phân trang
        $products = $query->paginate(12)->withQueryString();

        // 5. Lấy danh sách ID
        $userFavoriteIds = $user->favorites()->pluck('products.id')->toArray();

        // 6. Lấy Categories
        $categories = Category::where('status', 1)->orderBy('name')->get();

        // 7. Trả về mảng dữ liệu cho view
        return compact(
            'products',
            'sortLabels',
            'sortOption',
            'userFavoriteIds',
            'categories'
        );
    }

    /**
     * Thêm/xóa sản phẩm yêu thích và trả về trạng thái.
     *
     * @param User $user
     * @param int $productId
     * @return string ('added' hoặc 'removed')
     * @throws \Exception
     */
    public function toggleFavorite(User $user, int $productId): string
    {
        // Sử dụng DB::transaction cho an toàn
        return DB::transaction(function () use ($user, $productId) {
            $result = $user->favorites()->toggle($productId);

            // Xác định trạng thái
            if (count(Arr::get($result, 'attached', [])) > 0) {
                return 'added';
            }

            return 'removed';
        });
    }
}