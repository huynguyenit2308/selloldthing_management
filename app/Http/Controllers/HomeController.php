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

        $products = Product::publicListingBase()
            ->latest('created_at')
            ->take(12)
            ->get();

        $topReviewedProducts = Product::publicListingBase()
            ->whereHas('reviews')
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
