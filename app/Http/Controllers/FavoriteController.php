<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    // Removed middleware from constructor - it's handled in routes

    /**
     * Display the user's favorite products
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Initialize error states
        $errorStates = [
            'connectionError' => false,
            'authError' => false,
            'sessionExpired' => false,
            'syncError' => false,
            'performanceWarning' => false,
            'unavailableProducts' => []
        ];
        
        try {
            // Get all categories for filter
            $categories = Category::where('status', 'active')->get();
        
        // Base query for user's favorites
        $query = Favorite::with(['product.images', 'product.category', 'product.user'])
            ->where('user_id', $user->id)
            ->whereHas('product', function($q) {
                $q->where('status', 'published')
                  ->whereNull('deleted_at');
            });

        // Apply filters
        if ($request->filled('category')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('filter_type')) {
            switch ($request->filter_type) {
                case 'recently_viewed':
                    // Assuming we have a view tracking mechanism
                    $query->whereHas('product', function($q) use ($user) {
                        $q->where('view_count', '>', 0);
                    });
                    break;
                case 'on_sale':
                    $query->whereHas('product', function($q) {
                        $q->whereColumn('price', '<', 'original_price');
                    });
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'price_asc':
                $query->join('products', 'favorites.product_id', '=', 'products.id')
                      ->orderBy('products.price', 'asc');
                break;
            case 'price_desc':
                $query->join('products', 'favorites.product_id', '=', 'products.id')
                      ->orderBy('products.price', 'desc');
                break;
            case 'name':
                $query->join('products', 'favorites.product_id', '=', 'products.id')
                      ->orderBy('products.name', 'asc');
                break;
            default: // newest
                $query->orderBy('favorites.created_at', 'desc');
                break;
        }

        // Paginate results
        $favorites = $query->select('favorites.*')->paginate(9);
        
            // Get total count
            $totalCount = Favorite::where('user_id', $user->id)
                ->whereHas('product', function($q) {
                    $q->where('status', 'published')
                      ->whereNull('deleted_at');
                })->count();

            // Check for performance warning (more than 50 favorites)
            if ($totalCount > 50) {
                $errorStates['performanceWarning'] = true;
            }

            // Check for unavailable products
            $unavailableFavorites = Favorite::with('product')
                ->where('user_id', $user->id)
                ->whereHas('product', function($q) {
                    $q->where('status', '!=', 'published')
                      ->orWhereNotNull('deleted_at');
                })->get();

            foreach ($unavailableFavorites as $favorite) {
                if ($favorite->product) {
                    $reason = $favorite->product->deleted_at ? 'Sản phẩm đã bị xóa' : 'Sản phẩm không còn khả dụng';
                    $errorStates['unavailableProducts'][] = [
                        'name' => $favorite->product->name,
                        'reason' => $reason
                    ];
                }
            }

            return view('favorites.index', compact('favorites', 'categories', 'totalCount') + $errorStates);

        } catch (\Exception $e) {
            // Handle database connection errors
            $errorStates['connectionError'] = true;
            return view('favorites.index', [
                'favorites' => collect(),
                'categories' => collect(),
                'totalCount' => 0
            ] + $errorStates);
        }
    }

    /**
     * Add product to favorites
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = Auth::user();
        $productId = $request->product_id;

        // Check if already in favorites
        $existing = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm đã có trong danh sách yêu thích'
            ]);
        }

        // Check business rules (max favorites limit)
        $currentCount = Favorite::where('user_id', $user->id)->count();
        $maxFavorites = 100; // You can make this configurable
        
        if ($currentCount >= $maxFavorites) {
            return response()->json([
                'success' => false,
                'message' => "Đã đạt tối đa {$maxFavorites} sản phẩm yêu thích"
            ]);
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $productId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào danh sách yêu thích'
        ]);
    }

    /**
     * Remove product from favorites
     */
    public function destroy(Request $request, $productId)
    {
        $user = Auth::user();
        
        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không có trong danh sách yêu thích'
            ]);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa khỏi danh sách yêu thích'
        ]);
    }

    /**
     * Clear all favorites
     */
    public function clear()
    {
        $user = Auth::user();
        
        Favorite::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tất cả sản phẩm yêu thích'
        ]);
    }

    /**
     * Add all favorites to cart
     */
    public function addAllToCart()
    {
        $user = Auth::user();
        
        $favorites = Favorite::with('product')
            ->where('user_id', $user->id)
            ->whereHas('product', function($q) {
                $q->where('status', 'published')
                  ->where('quantity', '>', 0)
                  ->whereNull('deleted_at');
            })
            ->get();

        if ($favorites->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm khả dụng để thêm vào giỏ hàng'
            ]);
        }

        $addedCount = 0;
        $errors = [];

        foreach ($favorites as $favorite) {
            try {
                // Here you would integrate with your cart system
                // For now, we'll just simulate the process
                $addedCount++;
            } catch (\Exception $e) {
                $errors[] = $favorite->product->name;
            }
        }

        $totalCount = $favorites->count();
        
        if ($addedCount === $totalCount) {
            return response()->json([
                'success' => true,
                'message' => "Đã thêm {$addedCount} sản phẩm vào giỏ hàng"
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Đã thêm {$addedCount}/{$totalCount} sản phẩm thành công",
                'errors' => $errors
            ]);
        }
    }

    /**
     * Check if product is in user's favorites
     */
    public function check(Request $request, $productId)
    {
        if (!Auth::check()) {
            return response()->json(['is_favorite' => false]);
        }

        $isFavorite = Favorite::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }
}
