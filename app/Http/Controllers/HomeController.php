<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get();

        // Lấy 3 danh mục có nhiều sản phẩm nhất
        $topCategories = Category::where('status', 1)
            ->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderByDesc('products_count')
            ->take(3)
            ->get();

        $products = Product::published()
            ->with([
                'images' => function ($query) {
                    $query->orderBy('sort_order')->orderBy('created_at');
                },
                'category',
            ])
            ->latest('created_at')
            ->take(12)
            ->get();

        $topReviewedProducts = Product::published()
            ->whereHas('reviews')
            ->with([
                'images' => function ($query) {
                    $query->orderBy('sort_order')->orderBy('created_at');
                },
                'category',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->take(10)
            ->get();

        return view('home', [
            'categories' => $categories,
            'topCategories' => $topCategories,
            'products' => $products,
            'topReviewedProducts' => $topReviewedProducts,
        ]);
    }
}
