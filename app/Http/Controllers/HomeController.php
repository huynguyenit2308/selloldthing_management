<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->get();

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
            'products' => $products,
            'topReviewedProducts' => $topReviewedProducts,
        ]);
    }
}
