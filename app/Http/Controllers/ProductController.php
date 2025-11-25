<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // Validate price filter inputs
        try {
            $this->validatePriceFilter($request);
            $this->validateCategoryFilter($request);
            $this->validatePageFilter($request);
            $this->validateLocationFilter($request);
        } catch (ValidationException $e) {
            // Clear invalid page parameter when redirecting
            $input = $request->except(['page']);
            return redirect()
                ->route('products.index')
                ->withInput($input)
                ->withErrors($e->errors());
        }

        $categories = Category::orderBy('name')->get();

        $baseQuery = Product::query()->where('status', 'published');

        $products = (clone $baseQuery)
            ->with(['images' => function ($q) {
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
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $overflowRedirect = $this->guardAgainstOutOfRangePage($request, $products, 'products.index');

        if ($overflowRedirect) {
            return $overflowRedirect;
        }


        $priceBounds = (clone $baseQuery)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        $conditionOptions = Schema::hasColumn('products', 'condition')
            ? (clone $baseQuery)->whereNotNull('condition')->distinct()->orderBy('condition')->pluck('condition')
            : collect();
        $locationOptions = Schema::hasColumn('products', 'location')
            ? (clone $baseQuery)->whereNotNull('location')->distinct()->orderBy('location')->pluck('location')
            : collect();
        $userFavoriteIds = [];
        if (Auth::check()) {
            // Lấy TẤT CẢ ID yêu thích của user
            $userFavoriteIds = Auth::user()->favorites()->pluck('product_id')->toArray();
        }

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
            'userFavoriteIds' => $userFavoriteIds, // <-- THÊM MỚI DÒNG NÀY 
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);

        $categories = Category::orderBy('name')->get();

        $contactMethods = [
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'chat' => 'Chat trong ứng dụng',
            'other' => 'Khác',
        ];

        $conditions = [
            'new' => 'Mới',
            'like_new' => 'Như mới',
            'good' => 'Tốt',
            'fair' => 'Khá',
            'needs_repair' => 'Cần sửa',
        ];

        return view('product.create', [
            'user' => $user,
            'categories' => $categories,
            'contactMethods' => $contactMethods,
            'conditions' => $conditions,
            'maxImages' => 5,
            'submissionToken' => $this->issueProductSubmissionToken(),
        ]);
    }
    public function show(Product $product): View
    {
        $this->incrementProductViewCount($product);

        $product->load([
            'user',
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

        $similarProducts = Product::approved()
            ->with(['images' => function ($q) {
            $q->orderBy('created_at');
        }])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        $isFavorited = false; // Mặc định là chưa thích
        $userFavoriteIds = [];

        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (Auth::check()) {
            $userFavoriteIds = Auth::user()->favorites()->pluck('product_id')->toArray(); // <-- THÊM MỚI
            // Kiểm tra xem trong danh sách favorites của user có tồn tại product_id này không
            $isFavorited = Auth::user()->favorites()->where('product_id', $product->id)->exists();
        }
        return view('product.show', [
            'product' => $product,
            'averageRating' => $averageRating,
            'reviewsCount' => $reviewsCount,
            'similarProducts' => $similarProducts,
            'isFavorited' => $isFavorited, // <-- THÊM MỚI DÒNG NÀY
            'userFavoriteIds' => $userFavoriteIds, // <-- TRUYỀN BIẾN MỚI SANG VIEW
        ]);
    }

    private function incrementProductViewCount(Product $product): void
    {
        $sessionKey = "viewed_products.{$product->id}";

        if (!session()->has($sessionKey)) {
            $product->increment('view_count');
        }

        session()->put($sessionKey, now()->timestamp);
    }

    public function manage(Request $request): View|RedirectResponse
    {
        // Validate page parameter
        try {
            $this->validatePageFilter($request);
        } catch (ValidationException $e) {
            // Clear invalid page parameter when redirecting
            $input = $request->except(['page']);
            return redirect()
                ->route('products.manage')
                ->withInput($input)
                ->withErrors($e->errors());
        }

        $user = $request->user();

        if (!$user instanceof User) {
            $user = User::query()->first();
        }

        abort_if(!$user, 404, 'User not found');

        $statusFilter = $request->input('status', 'all');
        $sortOption = $request->input('sort', 'newest');
        $searchTerm = trim((string) $request->input('q'));

        $baseQuery = Product::with(['images' => function ($q) {
            $q->orderBy('created_at');
        }, 'category'])
            ->where('user_id', $user->id);

        $statusOptions = ['all', 'published', 'pending', 'hidden', 'sold'];

        if (in_array($statusFilter, array_diff($statusOptions, ['all']), true)) {
            $baseQuery->where('status', $statusFilter);
        }

        if ($searchTerm !== '') {
            $baseQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');

                if (Schema::hasColumn('products', 'description')) {
                    $query->orWhere('description', 'like', '%' . $searchTerm . '%');
                }
            });
        }

        $sortMappings = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
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

        $overflowRedirect = $this->guardAgainstOutOfRangePage($request, $products, 'products.manage');

        if ($overflowRedirect) {
            return $overflowRedirect;
        }

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

        $productLimit = (int) (config('selloldthing.product_limit') ?? 100);
        $isLimitReached = ($statistics['total'] ?? 0) >= $productLimit;
        $accountRestricted = (bool) session('account_restricted', false);

        return view('product.manage', [
            'user' => $user,
            'products' => $products,
            'statistics' => $statistics,
            'statusFilter' => $statusFilter,
            'statusOptions' => $statusOptions,
            'sortOption' => $sortOption,
            'searchTerm' => $searchTerm,
            'productLimit' => $productLimit,
            'isLimitReached' => $isLimitReached,
            'accountRestricted' => $accountRestricted,
            'loadError' => session('product_load_error'),
            'syncWarning' => session('product_sync_warning'),
            'conflictPayload' => session('product_conflict_payload'),
            'productActionBlocked' => session('product_action_blocked'),
            'productStatusSuccess' => session('product_status_success'),
            'productStatusError' => session('product_status_error'),
            'productDeleteSuccess' => session('product_delete_success'),
            'productDeleteError' => session('product_delete_error'),
            'productDeleteReason' => session('product_delete_reason'),
            'productBulkSuccess' => session('product_bulk_success'),
            'productBulkError' => session('product_bulk_error'),
            'productBulkErrors' => session('product_bulk_errors'),
        ]);
    }

    public function edit(Request $request, Product $product): View|RedirectResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);
        abort_if($product->user_id !== $user->id, 403, 'Bạn không có quyền chỉnh sửa sản phẩm này');

        // Nếu sản phẩm đã được cập nhật sau khi tab danh sách mở, quay về trang quản lý
        $clientVersion = (int) $request->query('version', 0);
        $currentVersion = $product->updated_at ? $product->updated_at->getTimestamp() : 0;

        if ($clientVersion > 0 && $currentVersion > 0 && $clientVersion < $currentVersion) {
            return redirect()
                ->route('products.manage')
                ->with('product_sync_warning', 'Sản phẩm đã được cập nhật ở một tab khác. Vui lòng xem lại danh sách sản phẩm mới nhất.');
        }

        $product->load(['images' => function ($query) {
            $query->orderBy('sort_order')->orderBy('created_at');
        }, 'category']);

        $categories = Category::orderBy('name')->get();
        $contactMethods = $this->contactMethodOptions();
        $conditions = $this->conditionOptions();

        [$locationCity, $locationDistrict] = $this->splitLocation($product->location);
        $selectedContactMethods = $product->contact_method ? array_filter(explode(',', $product->contact_method)) : [];

        $additionalInfo = $this->resolveAdditionalInfo($product->additional_info);

        $draft = $this->getProductDraft($user->id, $product->id);

        $isSold = $product->status === 'sold';

        return view('product.edit', [
            'user' => $user,
            'product' => $product,
            'categories' => $categories,
            'contactMethods' => $contactMethods,
            'conditions' => $conditions,
            'maxImages' => 5,
            'locationCity' => old('location_city', $draft['data']['location_city'] ?? $locationCity),
            'locationDistrict' => old('location_district', $draft['data']['location_district'] ?? $locationDistrict),
            'selectedContactMethods' => old('contact_methods', $draft['data']['contact_methods'] ?? $selectedContactMethods),
            'additionalInfo' => [
                'return_policy' => old('return_policy', $draft['data']['return_policy'] ?? $additionalInfo['return_policy']),
                'shipping_policy' => old('shipping_policy', $draft['data']['shipping_policy'] ?? $additionalInfo['shipping_policy']),
                'additional_note' => old('additional_note', $draft['data']['additional_note'] ?? $additionalInfo['additional_note']),
            ],
            'draft' => $draft,
            'isSold' => $isSold,
            'phoneVerified' => (bool) ($user->phone && $user->phone !== ''),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);
        abort_if($product->user_id !== $user->id, 403, 'Bạn không có quyền chỉnh sửa sản phẩm này');

        if ($product->status === 'sold') {
            return back()->withErrors([
                'general' => 'Không thể chỉnh sửa sản phẩm đã có người mua',
            ]);
        }

        // Optimistic locking: ngăn ghi đè thay đổi từ tab khác
        $clientVersion = (int) $request->input('version', 0);
        $currentVersion = $product->updated_at ? $product->updated_at->getTimestamp() : 0;

        if ($clientVersion > 0 && $currentVersion > 0 && $clientVersion < $currentVersion) {
            return back()
                ->withInput()
                ->with('product_sync_warning', 'Sản phẩm đã được cập nhật ở một tab khác. Vui lòng tải lại trang để xem và áp dụng thay đổi mới nhất.');
        }

        $validated = $this->validateProductUpdate($request, $product, $user);

        DB::beginTransaction();

        try {
            $product->fill([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'],
                'short_description' => $validated['additional_note'] ?? null,
                'condition' => $validated['condition'],
                'price' => $validated['price'],
                'original_price' => $validated['original_price'] ?? null,
                'quantity' => $validated['quantity'],
                'location' => $validated['location_city'] . ' - ' . $validated['location_district'],
                'contact_method' => implode(',', $validated['contact_methods']),
            ]);

            $additionalInfo = array_filter([
                'return_policy' => $validated['return_policy'] ?? null,
                'shipping_policy' => $validated['shipping_policy'] ?? null,
                'additional_note' => $validated['additional_note'] ?? null,
            ], fn($value) => $value !== null && $value !== '');

            $product->additional_info = !empty($additionalInfo)
                ? json_encode($additionalInfo, JSON_UNESCAPED_UNICODE)
                : null;

            $product->save();

            $this->syncProductImages(
                $product,
                $request->file('images', []),
                $validated['existing_images'] ?? [],
                $validated['remove_image_ids'] ?? []
            );

            DB::commit();

            Cache::forget($this->productDraftCacheKey($user->id, $product->id));

            return redirect()
                ->route('products.manage')
                ->with('product_status_success', 'Cập nhật sản phẩm thành công.');
        } catch (ValidationException $validationException) {
            DB::rollBack();

            throw $validationException;
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withInput()->withErrors([
                'general' => 'Cập nhật thất bại. Vui lòng thử lại sau',
            ]);
        }
    }

    public function autosave(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User || $product->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền lưu bản nháp sản phẩm này',
            ], 403);
        }

        if ($product->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lưu bản nháp cho sản phẩm đã có người mua',
            ], 422);
        }

        $payload = $request->only([
            'name',
            'category_id',
            'description',
            'condition',
            'price',
            'original_price',
            'quantity',
            'location_city',
            'location_district',
            'contact_methods',
            'return_policy',
            'shipping_policy',
            'additional_note',
        ]);

        $payload['saved_at'] = now()->toIso8601String();

        Cache::put(
            $this->productDraftCacheKey($user->id, $product->id),
            ['data' => $payload, 'saved_at' => $payload['saved_at']],
            now()->addMinutes(30)
        );

        return response()->json([
            'success' => true,
            'saved_at' => $payload['saved_at'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);

        if (!$this->consumeProductSubmissionToken($request->input('submission_token'))) {
            return back()
                ->withInput()
                ->withErrors([
                    'general' => 'Phiên gửi không hợp lệ hoặc đã được xử lý. Vui lòng tải lại trang và thử lại.',
                ]);
        }

        \Log::info('Bắt đầu tạo sản phẩm', ['user_id' => $user->id]);

        $validated = $this->validateProduct($request, $user);

        \Log::info('Validation thành công', ['images_count' => count($request->file('images', []))]);

        $creationLock = Cache::lock('product:create:' . $user->id, 5);
        $lockAcquired = $creationLock->get();

        if (!$lockAcquired) {
            return back()
                ->withInput()
                ->withErrors([
                    'general' => 'Yêu cầu tạo sản phẩm đang được xử lý. Vui lòng không nhấn lưu liên tục.',
                ]);
        }

        DB::beginTransaction();

        try {
            $product = Product::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'],
                'short_description' => $validated['short_description'] ?? null,
                'condition' => $validated['condition'],
                'price' => $validated['price'],
                'original_price' => $validated['original_price'] ?? null,
                'quantity' => $validated['quantity'],
                'location' => $validated['location_city'] . ' - ' . $validated['location_district'],
                'contact_method' => implode(',', $validated['contact_methods']),
                'status' => 'pending',
            ]);

            \Log::info('Sản phẩm đã tạo', ['product_id' => $product->id]);

            $this->storeProductImages($product, $request->file('images', []));

            \Log::info('Ảnh đã lưu thành công');

            DB::commit();

            // Gửi thông báo cho user đang theo dõi danh mục (sau khi commit transaction)
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyWatchersOfNewProduct($product);
            } catch (\Exception $e) {
                \Log::error('Lỗi khi gửi thông báo sản phẩm mới', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
                // Không throw exception để không ảnh hưởng đến việc tạo sản phẩm
            }

            return redirect()
                ->route('products.manage')
                ->with('product_status_success', 'Sản phẩm đã được gửi để duyệt.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            \Log::error('Lỗi khi tạo sản phẩm', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            report($exception);

            return back()->withInput()->withErrors([
                'general' => 'Không thể đăng sản phẩm. Vui lòng thử lại: ' . $exception->getMessage(),
            ]);
        } finally {
            if ($lockAcquired) {
                $creationLock->release();
            }
        }
    }

    protected function validateProduct(Request $request, User $user): array
    {
        $forbiddenKeywords = [
            'cấm',
            'illegal',
            'fake',
            'scam',
        ];

        $phoneVerified = (bool) ($user->phone && $user->phone !== '');

        $this->rejectWhitespaceOnlyText($request, [
            'name' => 'Tên sản phẩm',
            'description' => 'Mô tả sản phẩm',
            'location_city' => 'Tỉnh/Thành phố',
            'location_district' => 'Quận/Huyện',
        ]);

        $this->rejectFullWidthDigits($request, [
            'price' => 'Giá bán',
            'original_price' => 'Giá gốc',
            'quantity' => 'Số lượng',
        ]);

        $request->merge([
            'price' => $this->normalizePrice($request->input('price')),
            'original_price' => $this->normalizePrice($request->input('original_price')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'between:1,30', 'regex:/^[^<>|]+$/u'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['required', 'string', 'between:1,3000'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:8192'],
            'condition' => ['required', Rule::in(['new', 'like_new', 'good', 'fair', 'needs_repair'])],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'original_price' => ['nullable', 'integer', 'min:1000', 'max:999999999'],
            'quantity' => ['required', 'integer', 'min:0', 'max:999999'],
            'location_city' => ['required', 'string', 'between:2,100'],
            'location_district' => ['required', 'string', 'between:2,100'],
            'contact_methods' => ['required', 'array', 'min:1'],
            'contact_methods.*' => ['string', Rule::in(['phone', 'email', 'chat', 'other'])],
        ], [
            'name.regex' => 'Tên sản phẩm chứa ký tự không hợp lệ',
            'images.required' => 'Vui lòng chọn ít nhất 1 ảnh sản phẩm',
            'images.max' => 'Chỉ được upload tối đa 5 ảnh',
            'images.*.mimetypes' => 'File không đúng định dạng (chỉ chấp nhận JPG, PNG)',
            'images.*.max' => 'Kích thước file vượt quá 8MB',
            'condition.in' => 'Vui lòng chọn tình trạng sản phẩm hợp lệ',
            'original_price.integer' => 'Giá gốc phải là số nguyên',
            'original_price.min' => 'Giá gốc phải từ :min VND trở lên',
            'original_price.max' => 'Giá gốc không được lớn hơn :max',
            'contact_methods.required' => 'Vui lòng chọn hình thức liên hệ',
        ]);

        $descriptionLower = mb_strtolower($validated['description']);

        foreach ($forbiddenKeywords as $keyword) {
            if (str_contains($descriptionLower, $keyword)) {
                throw ValidationException::withMessages([
                    'description' => 'Mô tả chứa nội dung không phù hợp',
                ]);
            }
        }

        if (isset($validated['original_price']) && $validated['original_price'] < $validated['price']) {
            throw ValidationException::withMessages([
                'original_price' => 'Giá gốc phải lớn hơn hoặc bằng giá bán',
            ]);
        }

        if (in_array('phone', $validated['contact_methods'], true) && !$phoneVerified) {
            throw ValidationException::withMessages([
                'contact_methods' => 'Số điện thoại chưa được xác thực. Vui lòng cập nhật trong hồ sơ',
            ]);
        }

        return $validated;
    }

    protected function issueProductSubmissionToken(): string
    {
        $token = (string) Str::uuid();
        $tokens = collect(session('product_submission_tokens', []))
            ->filter(fn($value) => is_string($value))
            ->take(-4)
            ->values();

        $tokens->push($token);

        session(['product_submission_tokens' => $tokens->all()]);

        return $token;
    }

    protected function consumeProductSubmissionToken(?string $token): bool
    {
        if (!$token || !is_string($token)) {
            return false;
        }

        $tokens = collect(session('product_submission_tokens', []));
        $remaining = $tokens->reject(fn($stored) => $stored === $token)->values();

        session(['product_submission_tokens' => $remaining->all()]);

        return $tokens->count() !== $remaining->count();
    }

    protected function validateProductUpdate(Request $request, Product $product, User $user): array
    {
        $this->rejectWhitespaceOnlyText($request, [
            'name' => 'Tên sản phẩm',
            'description' => 'Mô tả sản phẩm',
            'location_city' => 'Tỉnh/Thành phố',
            'location_district' => 'Quận/Huyện',
        ]);

        $this->rejectFullWidthDigits($request, [
            'price' => 'Giá bán',
            'original_price' => 'Giá gốc',
            'quantity' => 'Số lượng',
        ]);

        $this->normalizeRequestPrices($request);

        $rules = [
            'name' => ['required', 'string', 'between:1,30', 'regex:/^[^<>|]+$/u'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'description' => ['required', 'string', 'between:1,3000'],
            'condition' => ['required', Rule::in(array_keys($this->conditionOptions()))],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'original_price' => ['nullable', 'integer', 'min:1000', 'max:999999999'],
            'quantity' => ['required', 'integer', 'min:0', 'max:999999'],
            'location_city' => ['required', 'string', 'between:2,100'],
            'location_district' => ['required', 'string', 'between:2,100'],
            'contact_methods' => ['required', 'array', 'min:1'],
            'contact_methods.*' => ['string', Rule::in(array_keys($this->contactMethodOptions()))],
            'return_policy' => ['nullable', 'string', 'max:500'],
            'shipping_policy' => ['nullable', 'string', 'max:500'],
            'additional_note' => ['nullable', 'string', 'max:500'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['integer', Rule::exists('product_images', 'id')->where('product_id', $product->id)],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', Rule::exists('product_images', 'id')->where('product_id', $product->id)],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
        ];

        $messages = [
            'name.regex' => 'Tên sản phẩm chứa ký tự không hợp lệ',
            'contact_methods.required' => 'Vui lòng chọn hình thức liên hệ',
            'images.*.mimetypes' => 'Chỉ chấp nhận file JPG, PNG, WebP',
            'images.*.max' => 'Kích thước file không được vượt quá 5MB',
            'original_price.integer' => 'Giá gốc phải là số nguyên',
            'original_price.min' => 'Giá gốc phải từ :min VND trở lên',
            'original_price.max' => 'Giá gốc không được lớn hơn :max',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $forbiddenKeywords = ['cấm', 'illegal', 'fake', 'scam'];

        $validator->after(function ($validator) use ($request, $product, $forbiddenKeywords, $user) {
            $description = (string) $request->input('description', '');
            $descriptionLower = mb_strtolower($description);

            foreach ($forbiddenKeywords as $keyword) {
                if ($descriptionLower !== '' && str_contains($descriptionLower, $keyword)) {
                    $validator->errors()->add('description', 'Mô tả chứa nội dung không phù hợp');
                    break;
                }
            }

            if (in_array('phone', (array) $request->input('contact_methods', []), true) && !($user->phone && $user->phone !== '')) {
                $validator->errors()->add('contact_methods', 'Tài khoản của bạn chưa được xác thực để upload ảnh');
            }

            $existingIds = collect($request->input('existing_images', []))
                ->map(static fn($id) => (int) $id)
                ->filter();
            $removeIds = collect($request->input('remove_image_ids', []))
                ->map(static fn($id) => (int) $id)
                ->filter();

            $totalExisting = ProductImage::where('product_id', $product->id)
                ->whereNotIn('id', $removeIds)
                ->count();

            $newImagesCount = count(array_filter($request->file('images', [])));

            $finalTotal = $totalExisting + $newImagesCount;

            if ($finalTotal === 0) {
                $validator->errors()->add('images', 'Vui lòng giữ lại ít nhất 1 ảnh sản phẩm');
            }

            if ($finalTotal > 5) {
                $validator->errors()->add('images', 'Chỉ được upload tối đa 5 ảnh');
            }

            if ($existingIds->isNotEmpty()) {
                $unknownIds = $existingIds
                    ->diff(ProductImage::where('product_id', $product->id)->pluck('id'));

                if ($unknownIds->isNotEmpty()) {
                    $validator->errors()->add('images', 'Thứ tự ảnh không hợp lệ');
                }
            }
        });

        $validated = $validator->validate();

        return $validated;
    }

    /**
     * @param array<int, UploadedFile|null> $files
     */
    protected function storeProductImages(Product $product, array $files): void
    {
        $sortOrder = 0;

        foreach (array_filter($files) as $file) {
            try {
                $imagePath = $this->storeImageFile($file);

                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imagePath,
                    'sort_order' => $sortOrder++,
                ]);
            } catch (\Throwable $exception) {
                \Log::error('Lỗi khi lưu ảnh sản phẩm', [
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);
                report($exception);

                throw ValidationException::withMessages([
                    'images' => 'Không thể tải ảnh lên: ' . $exception->getMessage(),
                ]);
            }
        }
    }

    protected function storeImageFile(UploadedFile $file): string
    {
        \Log::info('Bắt đầu lưu file', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        // Bỏ giới hạn kích thước tối thiểu
        // $imageInfo = getimagesize($file->getPathname());
        // if (!$imageInfo || $imageInfo[0] < 300 || $imageInfo[1] < 300) {
        //     \Log::warning('Ảnh quá nhỏ', ['size' => $imageInfo ? $imageInfo[0] . 'x' . $imageInfo[1] : 'unknown']);
        //     throw ValidationException::withMessages([
        //         'images' => 'Ảnh có kích thước quá nhỏ (tối thiểu 300x300px)',
        //     ]);
        // }

        $targetPath = 'product_uploads/' . date('Y/m');
        \Log::info('Đang lưu vào', ['path' => $targetPath]);

        $path = $file->store($targetPath, ['disk' => 'public']);

        \Log::info('Kết quả lưu file', ['path' => $path, 'success' => (bool)$path]);

        if (!$path) {
            \Log::error('Không thể lưu file vào storage');
            throw ValidationException::withMessages([
                'images' => 'Không thể lưu ảnh. Vui lòng thử lại sau.',
            ]);
        }

        return $path;
    }

    protected function syncProductImages(Product $product, array $newFiles, array $existingOrder, array $removeIds): void
    {
        $removeIds = collect($removeIds)->map(static fn($id) => (int) $id)->filter()->unique()->values();

        if ($removeIds->isNotEmpty()) {
            $imagesToDelete = ProductImage::where('product_id', $product->id)
                ->whereIn('id', $removeIds)
                ->get();

            foreach ($imagesToDelete as $image) {
                $this->deleteImageFile($image->url);
                $image->delete();
            }
        }

        $orderedIds = collect($existingOrder)
            ->map(static fn($id) => (int) $id)
            ->filter(fn($id) => !$removeIds->contains($id))
            ->values();

        if ($orderedIds->isEmpty()) {
            $orderedIds = ProductImage::where('product_id', $product->id)
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->pluck('id');
        }

        $sortOrder = 0;

        foreach ($orderedIds as $imageId) {
            ProductImage::where('product_id', $product->id)
                ->where('id', $imageId)
                ->update(['sort_order' => $sortOrder++]);
        }

        foreach (array_filter($newFiles) as $file) {
            $imageUrl = $this->storeImageFile($file);

            ProductImage::create([
                'product_id' => $product->id,
                'url' => $imageUrl,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    protected function validateCategoryFilter(Request $request): void
    {
        $categoryId = $request->input('category');
        
        if ($categoryId !== null && $categoryId !== '') {
            if (!Category::where('id', $categoryId)->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Danh mục không tồn tại',
                ]);
            }
        }
    }

    protected function validatePageFilter(Request $request): void
    {
        $page = $request->input('page');
        
        if ($page !== null && $page !== '') {
            if (!is_numeric($page) || (int)$page < 1) {
                throw ValidationException::withMessages([
                    'page' => 'Số trang không hợp lệ',
                ]);
            }
        }
    }

    protected function guardAgainstOutOfRangePage(Request $request, LengthAwarePaginator $paginator, string $routeName): ?RedirectResponse
    {
        $requestedPage = (int) $request->input('page', 1);
        $totalItems = $paginator->total();
        $lastPage = max(1, $paginator->lastPage());

        if (($totalItems === 0 && $requestedPage > 1) || ($totalItems > 0 && $requestedPage > $lastPage)) {
            $input = $request->except(['page']);
            $errorMessage = $totalItems > 0
                ? "Số trang không tồn tại. Trang cuối cùng hiện tại là {$lastPage}."
                : 'Không có dữ liệu cho trang đã yêu cầu. Đã chuyển bạn về trang đầu.';

            return redirect()
                ->route($routeName, $input)
                ->withInput(array_merge($input, ['page' => min($lastPage, 1)]))
                ->withErrors(['page' => $errorMessage]);
        }

        return null;
    }

    protected function validateLocationFilter(Request $request): void
    {
        $location = $request->input('location');

        if ($location !== null && $location !== '') {
            if (!Schema::hasColumn('products', 'location')) {
                throw ValidationException::withMessages([
                    'location' => 'Không thể lọc theo địa điểm vào lúc này',
                ]);
            }

            $locationExists = Product::query()
                ->where('status', 'published')
                ->where('location', $location)
                ->exists();

            if (!$locationExists) {
                throw ValidationException::withMessages([
                    'location' => 'Không tìm thấy địa chỉ phù hợp',
                ]);
            }
        }
    }


    protected function validatePriceFilter(Request $request): void
    {
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        // Check if one field is filled but the other is empty
        if (($priceMin !== null && $priceMin !== '' && ($priceMax === null || $priceMax === '')) ||
            ($priceMax !== null && $priceMax !== '' && ($priceMin === null || $priceMin === ''))) {
            throw ValidationException::withMessages([
                'price_filter' => 'Vui lòng nhập đầy đủ khoảng giá',
            ]);
        }

        // If both fields are filled, validate them
        if ($priceMin !== null && $priceMin !== '' && $priceMax !== null && $priceMax !== '') {
            // Check if inputs are numeric
            if (!is_numeric($priceMin) || !is_numeric($priceMax)) {
                throw ValidationException::withMessages([
                    'price_filter' => 'Vui lòng nhập giá hợp lệ',
                ]);
            }

            $minPrice = (float) $priceMin;
            $maxPrice = (float) $priceMax;

            // Check for negative values
            if ($minPrice < 0 || $maxPrice < 0) {
                throw ValidationException::withMessages([
                    'price_filter' => 'Giá không được nhỏ hơn 0',
                ]);
            }

            // Check if "from" price is greater than "to" price
            if ($minPrice > $maxPrice) {
                throw ValidationException::withMessages([
                    'price_filter' => 'Giá \'Từ\' không được lớn hơn giá \'Đến\'',
                ]);
            }
        }
    }


    protected function normalizePrice($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $numeric = preg_replace('/[^0-9]/', '', (string) $value);

        if ($numeric === '') {
            return null;
        }

        return (int) $numeric;
    }

    protected function normalizeRequestPrices(Request $request): void
    {
        $request->merge([
            'price' => $this->normalizePrice($request->input('price')),
            'original_price' => $this->normalizePrice($request->input('original_price')),
        ]);
    }

    protected function deleteImageFile(?string $url): void
    {
        if (!$url) {
            return;
        }

        $normalized = str_replace('\\', '/', trim($url));

        if ($normalized === '') {
            return;
        }

        // Nếu là URL đầy đủ, chuyển về đường dẫn tương đối
        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            $publicPrefix = rtrim(Storage::disk('public')->url(''), '/');

            if (!Str::startsWith($normalized, $publicPrefix)) {
                return;
            }

            $normalized = ltrim(Str::after($normalized, $publicPrefix), '/');
        }

        // Loại bỏ tiền tố storage/ nếu có
        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = ltrim(Str::after($normalized, 'storage/'), '/');
        }

        // Loại bỏ tiền tố public/ nếu có
        if (Str::startsWith($normalized, 'public/')) {
            $normalized = ltrim(Str::after($normalized, 'public/'), '/');
        }

        $normalized = ltrim($normalized, '/');

        if ($normalized === '') {
            return;
        }

        Storage::disk('public')->delete($normalized);
    }

    protected function containsFullWidthDigits($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return preg_match('/[\x{FF10}-\x{FF19}]/u', (string) $value) === 1;
    }

    protected function rejectFullWidthDigits(Request $request, array $fields): void
    {
        foreach ($fields as $field => $label) {
            $value = $request->input($field);

            if ($value === null || $value === '') {
                continue;
            }

            if ($this->containsFullWidthDigits($value)) {
                throw ValidationException::withMessages([
                    $field => sprintf('%s không được chứa số full-width. Vui lòng dùng số 0-9.', $label),
                ]);
            }
        }
    }

    protected function rejectWhitespaceOnlyText(Request $request, array $fields): void
    {
        foreach ($fields as $field => $label) {
            $value = $request->input($field);

            if ($value === null) {
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            $trimmed = $this->unicodeTrim($value);

            if ($trimmed === '') {
                throw ValidationException::withMessages([
                    $field => sprintf('%s không được chỉ chứa khoảng trắng.', $label),
                ]);
            }

            if ($trimmed !== $value) {
                $request->merge([$field => $trimmed]);
            }
        }
    }

    protected function unicodeTrim(string $value): string
    {
        $trimmed = preg_replace('/^\s+|\s+$/u', '', $value);

        if ($trimmed === null) {
            return trim($value);
        }

        return trim($trimmed);
    }

    protected function splitLocation(?string $location): array
    {
        if (!$location) {
            return ['', ''];
        }

        $parts = explode(' - ', $location, 2);

        return [
            trim($parts[0] ?? ''),
            trim($parts[1] ?? ''),
        ];
    }

    protected function resolveAdditionalInfo(?string $payload): array
    {
        if (!$payload) {
            return [
                'return_policy' => '',
                'shipping_policy' => '',
                'additional_note' => '',
            ];
        }

        $decoded = json_decode($payload, true);

        if (!is_array($decoded)) {
            return [
                'return_policy' => '',
                'shipping_policy' => '',
                'additional_note' => '',
            ];
        }

        return [
            'return_policy' => (string) ($decoded['return_policy'] ?? ''),
            'shipping_policy' => (string) ($decoded['shipping_policy'] ?? ''),
            'additional_note' => (string) ($decoded['additional_note'] ?? ''),
        ];
    }

    protected function contactMethodOptions(): array
    {
        return [
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'chat' => 'Chat trong ứng dụng',
            'other' => 'Khác',
        ];
    }

    protected function conditionOptions(): array
    {
        return [
            'new' => 'Mới',
            'like_new' => 'Như mới',
            'good' => 'Tốt',
            'fair' => 'Khá',
            'needs_repair' => 'Cần sửa',
        ];
    }

    protected function productDraftCacheKey(int $userId, int $productId): string
    {
        return sprintf('product-draft-%d-%d', $userId, $productId);
    }

    protected function getProductDraft(int $userId, int $productId): ?array
    {
        $draft = Cache::get($this->productDraftCacheKey($userId, $productId));

        if (is_array($draft) && isset($draft['data'])) {
            return $draft;
        }

        return null;
    }

    public function toggleVisibility(Request $request, Product $product)
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);
        abort_if($product->user_id !== $user->id, 403);

        $respond = function (bool $success, string $message, ?string $status = null) use ($request) {
            if ($request->wantsJson()) {
                $payload = ['success' => $success, 'message' => $message];

                if ($status !== null) {
                    $payload['status'] = $status;
                }

                return response()->json($payload, $success ? 200 : 422);
            }

            $flashKey = $success ? 'product_status_success' : 'product_status_error';

            return back()->with($flashKey, $message);
        };

        if (session('account_restricted', false)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn đang bị hạn chế tính năng đăng bán',
                ], 403);
            }

            return back()->with('product_action_blocked', 'Tài khoản của bạn đang bị hạn chế tính năng đăng bán');
        }

        if ($product->status === 'sold') {
            return $respond(false, 'Không thể thay đổi trạng thái sản phẩm đã bán');
        }

        if ($product->status === 'pending') {
            return $respond(false, 'Sản phẩm đang chờ duyệt, vui lòng đợi quản trị viên phê duyệt');
        }

        $nextStatus = match ($product->status) {
            'published' => 'hidden',
            'hidden' => 'published',
            default => null,
        };

        if ($nextStatus === null) {
            return $respond(false, 'Không thể thay đổi trạng thái sản phẩm');
        }

        try {
            $product->status = $nextStatus;
            $product->save();

            $message = $nextStatus === 'published'
                ? 'Sản phẩm đã được hiển thị trở lại'
                : 'Sản phẩm đã được chuyển sang trạng thái ẩn';

            return $respond(true, $message, $nextStatus);
        } catch (\Throwable $exception) {
            report($exception);

            return $respond(false, 'Không thể thay đổi trạng thái sản phẩm');
        }
    }

    public function checkDeleteConditions(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);
        abort_if($product->user_id !== $user->id, 403);

        $errors = [];
        $warnings = [];

        // Check hard blocks (cannot delete)
        if (session('account_restricted', false)) {
            $errors[] = 'Tài khoản của bạn đang bị hạn chế tính năng đăng bán';
        }

        // Check for pending orders
        $pendingOrders = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['pending', 'processing', 'confirmed']);
            })->count();

        if ($pendingOrders > 0) {
            $errors[] = 'Sản phẩm này đang có đơn hàng chờ xử lý, không thể xóa';
        }

        // Check for paid orders
        $paidOrders = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['paid', 'shipped', 'delivered']);
            })->count();

        if ($paidOrders > 0) {
            $errors[] = 'Sản phẩm đã được thanh toán, không thể xóa';
        }

        // Check for promotion/voucher usage (if applicable)
        // This would need to be implemented based on your voucher system

        // Check for soft warnings
        if ($product->view_count > 100) {
            $warnings[] = "Sản phẩm này đang được nhiều người quan tâm ({$product->view_count} lượt xem)";
        }

        // Check if product is in favorites/cart
        $favoritesCount = $product->favorites()->count();
        if ($favoritesCount > 0) {
            $warnings[] = "Sản phẩm đang được {$favoritesCount} người dùng lưu trong danh sách yêu thích";
        }

        // Check if product is newly posted (less than 24 hours)
        if ($product->created_at->diffInHours(now()) < 24) {
            $secondsAgo = $product->created_at->diffInSeconds(now());
            $hours = intdiv($secondsAgo, 3600);
            $minutes = intdiv($secondsAgo % 3600, 60);
            $seconds = $secondsAgo % 60;
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $warnings[] = "Sản phẩm mới đăng trong {$formattedDuration} giờ qua";
        }

        // Check daily delete limit (business rule)
        $dailyDeleteCount = Cache::get("user_delete_count_{$user->id}_" . now()->format('Y-m-d'), 0);
        $maxDailyDeletes = 10; // Configure this as needed

        if ($dailyDeleteCount >= $maxDailyDeletes) {
            $errors[] = 'Bạn đã vượt quá số lần xóa cho phép trong ngày';
        }

        return response()->json([
            'canDelete' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);
        abort_if($product->user_id !== $user->id, 403);

        $respond = function (bool $success, string $message, ?array $undoData = null) use ($request) {
            if ($request->wantsJson()) {
                $payload = ['success' => $success, 'message' => $message];
                if ($undoData) {
                    $payload['undoData'] = $undoData;
                }
                return response()->json($payload, $success ? 200 : 422);
            }

            $flashKey = $success ? 'product_delete_success' : 'product_delete_error';
            return back()->with($flashKey, $message);
        };

        if (session('account_restricted', false)) {
            return $respond(false, 'Tài khoản của bạn đang bị hạn chế tính năng đăng bán');
        }

        // Re-check delete conditions
        $pendingOrders = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['pending', 'processing', 'confirmed']);
            })->count();

        if ($pendingOrders > 0) {
            return $respond(false, 'Sản phẩm đang có đơn hàng chờ xử lý, không thể xóa');
        }

        $paidOrders = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['paid', 'shipped', 'delivered']);
            })->count();

        if ($paidOrders > 0) {
            return $respond(false, 'Sản phẩm đã được thanh toán, không thể xóa');
        }

        // Check daily delete limit
        $dailyDeleteCount = Cache::get("user_delete_count_{$user->id}_" . now()->format('Y-m-d'), 0);
        $maxDailyDeletes = 10;

        if ($dailyDeleteCount >= $maxDailyDeletes) {
            return $respond(false, 'Bạn đã vượt quá số lần xóa cho phép trong ngày');
        }

        try {
            DB::beginTransaction();

            // Store product data for undo functionality
            $undoData = [
                'product_id' => $product->id,
                'user_id' => $user->id,
                'product_data' => $product->toArray(),
                'images_data' => $product->images->toArray(),
                'timestamp' => now()->timestamp,
            ];

            // Store undo data in cache for 10 minutes
            Cache::put("undo_delete_{$product->id}_{$user->id}", $undoData, now()->addMinutes(10));

            // Soft delete or hard delete based on business rules
            $product->delete();

            // Increment daily delete count
            Cache::put(
                "user_delete_count_{$user->id}_" . now()->format('Y-m-d'),
                $dailyDeleteCount + 1,
                now()->endOfDay()
            );

            DB::commit();

            return $respond(true, 'Sản phẩm đã được xóa thành công', $undoData);
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return $respond(false, 'Không thể xóa sản phẩm. Vui lòng thử lại');
        }
    }

    public function undoDelete(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'user_id' => 'required|integer',
            'timestamp' => 'required|integer',
        ]);

        if ($validated['user_id'] !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này',
            ], 403);
        }

        $cacheKey = "undo_delete_{$validated['product_id']}_{$user->id}";
        $undoData = Cache::get($cacheKey);

        if (!$undoData || $undoData['timestamp'] !== $validated['timestamp']) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hoàn tác. Thời gian hoàn tác đã hết hoặc dữ liệu không hợp lệ',
            ]);
        }

        try {
            DB::beginTransaction();

            // Restore the product
            $product = Product::withTrashed()->find($validated['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm để khôi phục',
                ]);
            }

            $product->restore();

            // Restore images if they were deleted
            foreach ($undoData['images_data'] as $imageData) {
                ProductImage::withTrashed()
                    ->where('id', $imageData['id'])
                    ->restore();
            }

            // Remove undo data from cache
            Cache::forget($cacheKey);

            // Decrement daily delete count
            $dailyDeleteCount = Cache::get("user_delete_count_{$user->id}_" . now()->format('Y-m-d'), 0);
            if ($dailyDeleteCount > 0) {
                Cache::put(
                    "user_delete_count_{$user->id}_" . now()->format('Y-m-d'),
                    $dailyDeleteCount - 1,
                    now()->endOfDay()
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được khôi phục thành công',
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Không thể khôi phục sản phẩm. Vui lòng thử lại',
            ]);
        }
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if(!$user instanceof User, 403);

        if (session('account_restricted', false)) {
            return back()->with('product_action_blocked', 'Tài khoản của bạn đang bị hạn chế tính năng đăng bán');
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in(['show', 'hide', 'delete'])],
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['integer'],
        ]);

        $ids = array_unique($validated['product_ids']);

        $products = Product::where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->get();

        if ($products->isEmpty()) {
            return back()->with('product_bulk_error', 'Không tìm thấy sản phẩm được chọn');
        }

        $processedCount = 0;
        $errors = [];

        foreach ($products as $product) {
            try {
                switch ($validated['action']) {
                    case 'show':
                        if (in_array($product->status, ['hidden', 'pending'], true)) {
                            $product->status = 'published';
                            $product->save();
                            $processedCount++;
                        }
                        break;
                    case 'hide':
                        if ($product->status === 'published') {
                            $product->status = 'hidden';
                            $product->save();
                            $processedCount++;
                        }
                        break;
                    case 'delete':
                        if ($product->orderItems()->exists()) {
                            $errors[] = sprintf('Không thể xóa "%s" do đang có đơn hàng', $product->name);
                            continue 2;
                        }

                        $product->delete();
                        $processedCount++;
                        break;
                }
            } catch (\Throwable $exception) {
                report($exception);
                $errors[] = sprintf('Không thể xử lý sản phẩm "%s"', $product->name);
            }
        }

        if ($processedCount > 0) {
            return back()
                ->with('product_bulk_success', 'Đã xử lý ' . number_format($processedCount) . ' sản phẩm')
                ->with('product_bulk_errors', $errors);
        }

        if (!empty($errors)) {
            return back()
                ->with('product_bulk_error', 'Không có sản phẩm nào được cập nhật')
                ->with('product_bulk_errors', $errors);
        }

        return back()->with('product_bulk_error', 'Không có sản phẩm nào phù hợp để xử lý');
    }
}
