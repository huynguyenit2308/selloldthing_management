<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
}
