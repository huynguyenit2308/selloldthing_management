<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        $product->load([
            'images' => function ($q) {
                $q->orderBy('created_at');
            },
            'category',
            'reviews' => function ($q) {
                $q->with('user')->latest();
            },
        ]);

        $averageRating = round((float) $product->reviews->avg('rating'), 1);
        $reviewsCount = $product->reviews->count();

        $similarProducts = Product::with(['images' => function ($q) {
            $q->orderBy('created_at');
        }])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return view('product.show', [
            'product' => $product,
            'averageRating' => $averageRating,
            'reviewsCount' => $reviewsCount,
            'similarProducts' => $similarProducts,
        ]);
    }
}
