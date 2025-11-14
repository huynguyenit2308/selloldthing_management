<?php

namespace App\Http\Controllers;

use App\Helpers\CartCount;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SearchHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    private const SUGGESTION_TAGS = [
        'Laptop cũ',
        'Iphone 12',
        'Bàn ghế',
        'Máy giặt',
    ];

    public function page(Request $request)
    {
        $keyword = $this->normalizeKeyword((string) $request->input('q'));
        $sort = (string) $request->input('sort', 'relevance');

        $initialPayload = null;
        $errors = null;

        if ($keyword !== '') {
            try {
                $initialPayload = $this->performSearch($request, $keyword, $sort, true);
            } catch (ValidationException $exception) {
                $errors = $exception->errors();
            }
        }

        return view('search.index', [
            'initialKeyword' => $keyword,
            'initialSort' => $sort,
            'initialPayload' => $initialPayload,
            'initialErrors' => $errors,
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        return response()->json([
            'suggestions' => self::SUGGESTION_TAGS,
            'history' => SearchHistory::recentForRequest($request)->map(function (SearchHistory $history) {
                return [
                    'id' => $history->id,
                    'keyword' => $history->keyword,
                    'last_searched_at' => optional($history->last_searched_at)->toIso8601String(),
                ];
            }),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $query = $this->normalizeKeyword($request->input('q', ''));

        if ($query === '') {
            return response()->json([
                'items' => self::SUGGESTION_TAGS,
            ]);
        }

        $staticMatches = collect(self::SUGGESTION_TAGS)
            ->filter(fn (string $item) => stripos($item, $query) !== false)
            ->values();

        $dynamicMatches = Product::suggestionQuery($query)->pluck('name');

        $merged = $staticMatches
            ->concat($dynamicMatches)
            ->unique()
            ->values()
            ->take(10);

        return response()->json([
            'items' => $merged,
        ]);
    }

    public function results(Request $request): JsonResponse
    {
        $keyword = $this->normalizeKeyword((string) $request->input('q'));
        $sort = (string) $request->input('sort', 'relevance');

        $payload = $this->performSearch($request, $keyword, $sort, true);

        return response()->json($payload);
    }

    public function history(Request $request): JsonResponse
    {
        $history = SearchHistory::recentForRequest($request)->map(function (SearchHistory $history) {
            return [
                'id' => $history->id,
                'keyword' => $history->keyword,
                'last_searched_at' => optional($history->last_searched_at)->toIso8601String(),
            ];
        });

        return response()->json(['history' => $history]);
    }

    public function clearHistory(Request $request): JsonResponse
    {
        $query = SearchHistory::query();

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        } else {
            $query->where('session_id', $request->session()->getId());
        }

        $query->delete();

        return response()->json(['status' => 'ok']);
    }

    public function destroyHistory(Request $request, SearchHistory $history): JsonResponse
    {
        abort_unless($history->belongsToRequest($request), 403);

        $history->delete();

        return response()->json(['status' => 'ok']);
    }

    public function toggleFavorite(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để lưu yêu thích.',
            ], 401);
        }

        $favorite = Favorite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            $status = 'added';
        }

        return response()->json([
            'status' => $status,
        ]);
    }

    public function addToCart(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.',
            ], 401);
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        $order = Order::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            ['total_price' => 0]
        );

        $orderItem = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->first();

        if ($orderItem) {
            $orderItem->quantity += $quantity;
            $orderItem->status = 'pending';
            $orderItem->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'status' => 'pending',
            ]);
        }

        $order->total_price = $order->items()
            ->where('status', 'pending')
            ->with('product')
            ->get()
            ->sum(fn (OrderItem $item) => $item->quantity * ($item->product->price ?? 0));

        $order->save();

        $cartCount = CartCount::updateCartCount();

        return response()->json([
            'status' => 'ok',
            'cart_count' => $cartCount,
        ]);
    }

    private function normalizeKeyword(string $keyword): string
    {
        $keyword = trim(preg_replace('/\s+/u', ' ', $keyword));
        return mb_substr($keyword, 0, 100);
    }

    private function performSearch(Request $request, string $keyword, string $sort, bool $recordHistory = false): array
    {
        $this->validateKeywordOrFail($keyword);

        if ($recordHistory) {
            SearchHistory::recordFromRequest($request, $keyword);
            SearchHistory::pruneForRequest($request);
        }

        $page = (int) max(1, (int) $request->input('page', 1));

        $query = Product::searchQuery($keyword);

        Product::applySearchSort($query, $sort, $keyword);

        /** @var LengthAwarePaginator $products */
        $products = $query->paginate(4, ['*'], 'page', $page);
        $productsCollection = collect($products->items());

        $user = $request->user();
        $favoriteIds = [];

        if ($user && $productsCollection->isNotEmpty()) {
            $favoriteIds = Favorite::query()
                ->where('user_id', $user->id)
                ->whereIn('product_id', $productsCollection->pluck('id'))
                ->pluck('product_id')
                ->all();
        }

        $results = $productsCollection->map(function (Product $product) use ($favoriteIds) {
            $image = optional($product->images->first())->image_url ?? asset('images/product_1.png');

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'condition' => $product->condition,
                'location' => $product->location,
                'image' => $image,
                'is_favorited' => in_array($product->id, $favoriteIds, true),
                'url' => route('products.show', $product),
            ];
        })->values();

        return [
            'keyword' => $keyword,
            'sort' => $sort,
            'results' => $results,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ];
    }

    /**
     * @throws ValidationException
     */
    private function validateKeywordOrFail(string $keyword): void
    {
        if ($keyword === '') {
            throw ValidationException::withMessages([
                'q' => __('Vui lòng nhập ít nhất 2 ký tự'),
            ])->status(422);
        }

        if (mb_strlen($keyword) < 2) {
            throw ValidationException::withMessages([
                'q' => __('Vui lòng nhập ít nhất 2 ký tự'),
            ])->status(422);
        }

        if (!preg_match('/^[\pL\pN\s\-\_\.]+$/u', $keyword)) {
            throw ValidationException::withMessages([
                'q' => __('Từ khóa chứa ký tự không hợp lệ'),
            ])->status(422);
        }
    }
}
