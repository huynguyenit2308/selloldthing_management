<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use App\Exports\InventoryExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;
    private const MAX_STOCK_QUANTITY = 10000;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->validatePageParameter($request);
        } catch (ValidationException $e) {
            $input = $request->except(['page']);

            return redirect()
                ->route('account.inventory')
                ->withInput($input)
                ->withErrors($e->errors());
        }

        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        $statusFilter = $request->input('status', 'all');
        $sortOption = $request->input('sort', 'newest');

        $productsQuery = Product::query()
            ->with(['images' => function ($query) {
                $query->orderBy('created_at');
            }])
            ->where('user_id', $user->id);

        [$sortColumn, $sortDirection] = $this->resolveSortOption($sortOption);
        $productsQuery->orderBy($sortColumn, $sortDirection);

        $products = $productsQuery->get();

        $soldCounts = $this->loadSoldCounts($products->pluck('id'));

        $productCollection = $products->map(function (Product $product) use ($soldCounts) {
            $sold = (int) ($soldCounts[$product->id] ?? 0);
            $inventoryStatus = $this->determineInventoryStatus($product->quantity, $sold);

            $firstImage = $product->images->first();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->short_description,
                'price' => (float) $product->price,
                'price_formatted' => $this->formatCurrency($product->price),
                'quantity' => (int) $product->quantity,
                'sold' => $sold,
                'image' => $firstImage?->url,
                'image_url' => $firstImage?->image_url,
                'placeholder' => mb_substr($product->name ?? 'Sản phẩm', 0, 1),
                'inventory_status' => $inventoryStatus,
                'created_at' => $product->created_at,
            ];
        });

        $allowedStatuses = ['all', 'in_stock', 'low_stock', 'out_of_stock', 'value'];
        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'all';
        }

        $normalizedFilter = $statusFilter === 'value' ? 'all' : $statusFilter;

        $filteredProducts = $productCollection->filter(function (array $product) use ($normalizedFilter) {
            if ($normalizedFilter === 'all') {
                return true;
            }

            return $product['inventory_status']['key'] === $normalizedFilter;
        })->values();

        $perPage = 5;
        $requestedPage = (int) $request->input('page', 1);
        $currentPage = max(1, $requestedPage);
        $total = $filteredProducts->count();
        $maxPage = max(1, (int) ceil($total / $perPage));

        if (($total === 0 && $requestedPage > 1) || ($total > 0 && $requestedPage > $maxPage)) {
            $input = $request->except(['page']);
            $errorMessage = $total > 0
                ? "Số trang không tồn tại. Trang cuối cùng hiện tại là {$maxPage}."
                : 'Không có dữ liệu cho trang đã yêu cầu. Đã chuyển bạn về trang đầu.';

            return redirect()
                ->route('account.inventory', $input)
                ->withInput(array_merge($input, ['page' => min($maxPage, 1)]))
                ->withErrors(['page' => $errorMessage]);
        }

        $pageItems = $filteredProducts
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $paginator = new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $inventoryStats = $this->calculateInventoryStats($productCollection);

        $sortOptions = [
            'newest' => 'Mới nhất',
            'oldest' => 'Cũ nhất',
            'price_desc' => 'Giá cao nhất',
            'price_asc' => 'Giá thấp nhất',
            'name_asc' => 'A-Z',
            'name_desc' => 'Z-A',
        ];

        $statCards = [
            [
                'label' => 'Tổng sản phẩm',
                'value' => $inventoryStats['total_products'],
                'display_value' => number_format($inventoryStats['total_products']),
                'status' => 'all',
            ],
            [
                'label' => 'Sản phẩm có hàng',
                'value' => $inventoryStats['in_stock'],
                'display_value' => number_format($inventoryStats['in_stock']),
                'status' => 'in_stock',
            ],
            [
                'label' => 'Sản phẩm sắp hết',
                'value' => $inventoryStats['low_stock'],
                'display_value' => number_format($inventoryStats['low_stock']),
                'status' => 'low_stock',
            ],
            [
                'label' => 'Tổng giá trị tồn kho',
                'value' => $inventoryStats['inventory_value'],
                'display_value' => $this->formatCurrency($inventoryStats['inventory_value']),
                'status' => 'value',
            ],
        ];

        return view('account.inventory', [
            'products' => $pageItems,
            'paginator' => $paginator,
            'statCards' => $statCards,
            'inventoryStats' => $inventoryStats,
            'statusFilter' => $statusFilter,
            'sortOptions' => $sortOptions,
            'selectedSort' => $sortOption,
            'lowStockThreshold' => self::LOW_STOCK_THRESHOLD,
            'maxStock' => self::MAX_STOCK_QUANTITY,
            'hasProducts' => $total > 0,
        ]);
    }

    protected function validatePageParameter(Request $request): void
    {
        $page = $request->input('page');

        if ($page !== null && $page !== '') {
            if (!is_numeric($page) || (int) $page < 1) {
                throw ValidationException::withMessages([
                    'page' => 'Số trang không hợp lệ',
                ]);
            }
        }
    }

    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user || $product->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật sản phẩm này.',
            ], 403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:' . self::MAX_STOCK_QUANTITY],
        ], [
            'quantity.required' => 'Vui lòng nhập số lượng tồn kho.',
            'quantity.integer' => 'Vui lòng nhập số nguyên dương.',
            'quantity.min' => 'Số lượng tồn kho không được âm.',
            'quantity.max' => 'Số lượng vượt quá giới hạn cho phép.',
        ]);

        $soldCount = $this->loadSoldCounts([$product->id])[$product->id] ?? 0;

        if ($validated['quantity'] < $soldCount) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng không thể nhỏ hơn số lượng đã bán.',
                'limit' => $soldCount,
            ], 422);
        }

        $product->quantity = $validated['quantity'];
        $product->save();

        $inventoryStatus = $this->determineInventoryStatus($product->quantity, $soldCount);

        $updatedStats = $this->calculateInventoryStats($this->loadUserProducts($user->id));

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tồn kho thành công.',
            'product' => [
                'id' => $product->id,
                'quantity' => (int) $product->quantity,
                'inventory_status' => $inventoryStatus,
            ],
            'stats' => $updatedStats,
        ]);
    }

    public function bulkSave(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên đăng nhập đã hết hạn.',
            ], 401);
        }

        $items = $request->input('items', []);

        if (!is_array($items) || empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu để lưu.',
            ], 422);
        }

        $productIds = collect($items)->pluck('id')->unique()->values();

        $products = Product::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        if ($products->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm hợp lệ để cập nhật.',
            ], 404);
        }

        $soldCounts = $this->loadSoldCounts($productIds);
        $errors = [];
        $updatedProducts = [];

        DB::beginTransaction();

        try {
            foreach ($items as $payload) {
                $productId = $payload['id'] ?? null;
                $quantity = $payload['quantity'] ?? null;

                if (!$productId || !is_numeric($quantity)) {
                    $errors[] = 'Dữ liệu sản phẩm không hợp lệ.';
                    continue;
                }

                $quantity = (int) $quantity;

                if ($quantity < 0) {
                    $errors[] = "Số lượng tồn kho không được âm (ID: {$productId}).";
                    continue;
                }

                if ($quantity > self::MAX_STOCK_QUANTITY) {
                    $errors[] = "Số lượng vượt quá giới hạn cho phép (ID: {$productId}).";
                    continue;
                }

                $product = $products->get($productId);

                if (!$product) {
                    $errors[] = "Sản phẩm #{$productId} không tồn tại.";
                    continue;
                }

                $soldCount = (int) ($soldCounts[$productId] ?? 0);

                if ($quantity < $soldCount) {
                    $errors[] = "Số lượng của '{$product->name}' không thể nhỏ hơn số lượng đã bán ({$soldCount}).";
                    continue;
                }

                if ($product->quantity == $quantity) {
                    continue;
                }

                $product->quantity = $quantity;
                $product->save();

                $updatedProducts[] = [
                    'id' => $product->id,
                    'quantity' => $quantity,
                    'inventory_status' => $this->determineInventoryStatus($quantity, $soldCount),
                ];
            }

            if ($errors) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Không thể lưu tất cả thay đổi.',
                    'errors' => $errors,
                ], 422);
            }

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu dữ liệu. Vui lòng thử lại sau.',
            ], 500);
        }

        $updatedStats = $this->calculateInventoryStats($this->loadUserProducts($user->id));

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu tất cả thay đổi.',
            'products' => $updatedProducts,
            'stats' => $updatedStats,
        ]);
    }

    public function export(Request $request): StreamedResponse|RedirectResponse|BinaryFileResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Vui lòng đăng nhập.');
        }

        $products = $this->loadUserProducts($user->id);

        if ($products->isEmpty()) {
            return redirect()
                ->back()
                ->with('inventory_export_error', 'Không có dữ liệu để xuất báo cáo.');
        }

        $format = strtolower((string) $request->input('format', 'xlsx'));
        $allowedFormats = ['xlsx', 'csv', 'xls'];

        if (!in_array($format, $allowedFormats, true)) {
            $format = 'xlsx';
        }

        $filename = 'bao_cao_ton_kho_' . now()->format('Ymd_His') . '.' . $format;

        $exportFormat = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;

        return Excel::download(new InventoryExport($products), $filename, $exportFormat);
    }

    private function resolveSortOption(string $option): array
    {
        $mapping = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'price_desc' => ['price', 'desc'],
            'price_asc' => ['price', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
        ];

        return $mapping[$option] ?? $mapping['newest'];
    }

    private function loadSoldCounts($productIds): array
    {
        $ids = collect($productIds)->filter()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $query = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereIn('product_id', $ids);

        if (Schema::hasColumn('order_items', 'status')) {
            $query->where('status', '!=', 'cancelled');
        }

        return $query
            ->groupBy('product_id')
            ->pluck('total_quantity', 'product_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    private function determineInventoryStatus(int $quantity, int $sold): array
    {
        if ($quantity <= 0) {
            return [
                'key' => 'out_of_stock',
                'label' => 'Hết hàng',
                'css' => 'status-out',
            ];
        }

        if ($quantity <= self::LOW_STOCK_THRESHOLD) {
            return [
                'key' => 'low_stock',
                'label' => 'Sắp hết',
                'css' => 'status-low',
            ];
        }

        return [
            'key' => 'in_stock',
            'label' => 'Còn hàng',
            'css' => 'status-in',
        ];
    }

    private function formatCurrency($value): string
    {
        return number_format((float) $value, 0, ',', '.') . ' VND';
    }

    private function calculateInventoryStats(Collection $products): array
    {
        $totalProducts = $products->count();
        $inStock = $products->filter(fn (array $product) => $product['quantity'] > 0)->count();
        $lowStock = $products->filter(fn (array $product) => $product['quantity'] > 0 && $product['quantity'] <= self::LOW_STOCK_THRESHOLD)->count();
        $inventoryValue = $products->reduce(function ($carry, array $product) {
            return $carry + ($product['price'] * $product['quantity']);
        }, 0.0);

        return [
            'total_products' => $totalProducts,
            'in_stock' => $inStock,
            'low_stock' => $lowStock,
            'inventory_value' => $inventoryValue,
            'inventory_value_formatted' => $this->formatCurrency($inventoryValue),
        ];
    }

    private function loadUserProducts(int $userId): Collection
    {
        $products = Product::query()
            ->with(['images' => function ($query) {
                $query->orderBy('created_at');
            }])
            ->where('user_id', $userId)
            ->get();

        $soldCounts = $this->loadSoldCounts($products->pluck('id'));

        return $products->map(function (Product $product) use ($soldCounts) {
            $sold = (int) ($soldCounts[$product->id] ?? 0);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => (int) $product->quantity,
                'sold' => $sold,
                'inventory_status' => $this->determineInventoryStatus($product->quantity, $sold),
            ];
        });
    }
}
