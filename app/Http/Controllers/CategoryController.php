<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status'); // active|inactive|null
        $categoriesQuery = Category::query()
            ->select(['id', 'name', 'description', 'image', 'status', 'created_at']);

        if ($q !== '') {
            $categoriesQuery->where('name', 'like', "%{$q}%");
        }

        $statusMap = [
            'active' => 1,
            'inactive' => 0,
        ];

        if (array_key_exists($status, $statusMap)) {
            $categoriesQuery->where('status', $statusMap[$status]);
        }

        $categories = $categoriesQuery->orderByDesc('created_at')->paginate(10)->withQueryString();
        

        return view('admin.categories.index', [
            'categories' => $categories,
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();
        } catch (Exception $e) {
            Log::error('CATEGORY_DELETE_FAILED: ' . $e->getMessage());

            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_DELETE_FAILED: Không thể xóa danh mục, vui lòng thử lại sau']);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Danh mục đã được xóa thành công');
    }
}
