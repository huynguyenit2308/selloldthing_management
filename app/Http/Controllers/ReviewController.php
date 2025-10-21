<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;

class ReviewController extends Controller
{
    public function create($id)
    {
        $product = Product::findOrFail($id);
        return view('product.show', compact('product'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);
    
        $review = Review::create([
            'product_id' => $id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);
    
        // Load quan hệ user để lấy tên user luôn
        $review->load('user');
    
        return response()->json([
            'success' => true,
            'review' => $review,
        ]);
    }
    
    public function showReviews($id)
    {
        $product = Product::with(['reviews.user', 'images', 'category'])->findOrFail($id);

        $averageRating = round((float) $product->reviews->avg('rating'), 1);
        $reviewsCount = $product->reviews->count();

        $similarProducts = Product::with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return view('product.show', compact('product', 'averageRating', 'reviewsCount', 'similarProducts'));
    }
}
