<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with(['images' => function ($q) {
            $q->orderBy('created_at');
        }, 'category'])
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category_id', (int) $request->input('category'));
            })
            ->when($request->filled('price_min'), function ($q) use ($request) {
                $q->where('price', '>=', (float) $request->input('price_min'));
            })
            ->when($request->filled('price_max'), function ($q) use ($request) {
                $q->where('price', '<=', (float) $request->input('price_max'));
            })
            ->when($request->filled('condition') && Schema::hasColumn('products', 'condition'), function ($q) use ($request) {
                $q->where('condition', $request->input('condition'));
            })
            ->when($request->filled('location') && Schema::hasColumn('products', 'location'), function ($q) use ($request) {
                $q->where('location', $request->input('location'));
            })
            ->orderByDesc('created_at');

        $products = $query->paginate(12)->withQueryString();

        $priceBounds = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $conditionOptions = Schema::hasColumn('products', 'condition')
            ? Product::whereNotNull('condition')->distinct()->orderBy('condition')->pluck('condition')
            : collect();
        $locationOptions = Schema::hasColumn('products', 'location')
            ? Product::whereNotNull('location')->distinct()->orderBy('location')->pluck('location')
            : collect();

        return view('product.index', [
            'categories' => $categories,
            'products' => $products,
            'filters' => [
                'category' => $request->input('category'),
                'price_min' => $request->input('price_min'),
                'price_max' => $request->input('price_max'),
                'condition' => $request->input('condition'),
                'location' => $request->input('location'),
            ],
            'priceBounds' => $priceBounds,
            'conditionOptions' => $conditionOptions,
            'locationOptions' => $locationOptions,
        ]);
    }
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

    public function manage(Request $request): View
    {
        $user = $request->user();

        if (!$user instanceof User) {
            $user = User::query()->first();
        }

        abort_if(!$user, 404, 'User not found');

        $statusFilter = $request->input('status', 'all');
        $sortOption = $request->input('sort', 'newest');

        $baseQuery = Product::with(['images' => function ($q) {
            $q->orderBy('created_at');
        }, 'category'])
            ->where('user_id', $user->id);

        $statusOptions = ['all', 'published', 'pending', 'hidden', 'sold'];

        if (in_array($statusFilter, array_diff($statusOptions, ['all']), true)) {
            $baseQuery->where('status', $statusFilter);
        }

        $sortMappings = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'views_desc' => ['view_count', 'desc'],
            'views_asc' => ['view_count', 'asc'],
        ];

        [$sortColumn, $sortDirection] = $sortMappings[$sortOption] ?? $sortMappings['newest'];

        $products = $baseQuery
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Product::select('status', DB::raw('COUNT(*) as total'))
            ->where('user_id', $user->id)
            ->groupBy('status')
            ->pluck('total', 'status');

        $statistics = [
            'total' => Product::where('user_id', $user->id)->count(),
            'published' => (int) ($statusCounts['published'] ?? 0),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'hidden' => (int) ($statusCounts['hidden'] ?? 0),
            'sold' => (int) ($statusCounts['sold'] ?? 0),
        ];

        return view('product.manage', [
            'user' => $user,
            'products' => $products,
            'statistics' => $statistics,
            'statusFilter' => $statusFilter,
            'statusOptions' => $statusOptions,
            'sortOption' => $sortOption,
        ]);
    }
}
