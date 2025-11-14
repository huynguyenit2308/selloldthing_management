<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'category_id', 'approval_status']);

        $products = Product::adminListing($filters)
            ->paginate(10)
            ->withQueryString();
        $categories = Category::where('status', 1)->orderBy('name')->get();

        // Count pending products
        $pendingCount = Product::pending()->count();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'q' => $filters['q'] ?? '',
            'status' => $filters['status'] ?? null,
            'category_id' => $filters['category_id'] ?? null,
            'approval_status' => $filters['approval_status'] ?? null,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function create()
    {
        $categories = Category::where('status', 1)->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'condition' => 'nullable|string',
            'location' => 'nullable|string',
            'seller_name' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'status' => 'required|in:published,draft,hidden',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $validated['user_id'] = auth()->id() ?? 1; // Default to admin user
            $product = Product::create($validated);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Sản phẩm đã được thêm thành công');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PRODUCT_CREATE_FAILED: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['system' => 'Không thể thêm sản phẩm, vui lòng thử lại sau']);
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', 1)->orderBy('name')->get();
        $product->load(['images', 'category']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'condition' => 'nullable|string',
            'location' => 'nullable|string',
            'seller_name' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'status' => 'required|in:published,draft,hidden',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',
        ]);

        try {
            DB::beginTransaction();

            $product->update($validated);

            // Delete selected images
            if ($request->has('delete_images')) {
                $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)
                    ->where('product_id', $product->id)
                    ->get();

                foreach ($imagesToDelete as $image) {
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    $image->delete();
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $existingImagesCount = $product->images()->count();
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => $existingImagesCount === 0 && $index === 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Sản phẩm đã được cập nhật thành công');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PRODUCT_UPDATE_FAILED: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['system' => 'Không thể cập nhật sản phẩm, vui lòng thử lại sau']);
        }
    }

    public function destroy(Request $request, Product $product)
    {
        try {
            // Check if product has orders
            $orderCount = $product->orderItems()->count();
            
            if ($orderCount > 0) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'PRODUCT_IN_USE',
                    'message' => 'Không thể xóa sản phẩm đang có trong đơn hàng',
                    'data' => ['order_count' => $orderCount]
                ], 400);
            }

            DB::beginTransaction();

            // Delete product images
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            // Delete product
            $product->delete();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được xóa thành công'
                ]);
            }

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Sản phẩm đã được xóa thành công');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PRODUCT_DELETE_FAILED: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'SERVER_ERROR',
                    'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau.'
                ], 500);
            }

            return redirect()
                ->route('admin.products.index')
                ->withErrors(['system' => 'Không thể xóa sản phẩm, vui lòng thử lại sau']);
        }
    }

    // Approve product
    public function approve(Request $request, Product $product)
    {
        try {
            $product->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'status' => 'published',
                'rejection_reason' => null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được duyệt thành công'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Sản phẩm đã được duyệt thành công');
        } catch (Exception $e) {
            Log::error('PRODUCT_APPROVE_FAILED: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra'
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['system' => 'Không thể duyệt sản phẩm']);
        }
    }

    // Reject product
    public function reject(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $product->update([
                'is_approved' => false,
                'status' => 'hidden',
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã bị từ chối'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Sản phẩm đã bị từ chối');
        } catch (Exception $e) {
            Log::error('PRODUCT_REJECT_FAILED: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra'
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['system' => 'Không thể từ chối sản phẩm']);
        }
    }

    // Toggle featured status
    public function toggleFeatured(Request $request, Product $product)
    {
        try {
            $isFeatured = !$product->is_featured;
            $featuredUntil = null;

            if ($isFeatured && $request->has('days')) {
                $days = (int) $request->input('days', 7);
                $featuredUntil = now()->addDays($days);
            }

            $product->update([
                'is_featured' => $isFeatured,
                'featured_until' => $featuredUntil,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'is_featured' => $isFeatured,
                    'message' => $isFeatured ? 'Đã ghim sản phẩm nổi bật' : 'Đã bỏ ghim sản phẩm'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', $isFeatured ? 'Đã ghim sản phẩm nổi bật' : 'Đã bỏ ghim sản phẩm');
        } catch (Exception $e) {
            Log::error('PRODUCT_TOGGLE_FEATURED_FAILED: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra'
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['system' => 'Không thể thay đổi trạng thái ghim']);
        }
    }

    // Update product status
    public function updateStatus(Request $request, Product $product)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,published,hidden,sold',
        ]);

        try {
            $product->update([
                'status' => $validated['status'],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã cập nhật trạng thái sản phẩm'
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Đã cập nhật trạng thái sản phẩm');
        } catch (Exception $e) {
            Log::error('PRODUCT_UPDATE_STATUS_FAILED: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra'
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['system' => 'Không thể cập nhật trạng thái']);
        }
    }

    // Extend expiration
    public function extendExpiration(Request $request, Product $product)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $expiresAt = $product->expires_at ?? now();
            $newExpiresAt = $expiresAt->addDays($validated['days']);

            $product->update([
                'expires_at' => $newExpiresAt,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã gia hạn {$validated['days']} ngày",
                    'expires_at' => $newExpiresAt->format('d/m/Y H:i')
                ]);
            }

            return redirect()
                ->back()
                ->with('success', "Đã gia hạn {$validated['days']} ngày");
        } catch (Exception $e) {
            Log::error('PRODUCT_EXTEND_FAILED: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã có lỗi xảy ra'
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['system' => 'Không thể gia hạn sản phẩm']);
        }
    }
}
