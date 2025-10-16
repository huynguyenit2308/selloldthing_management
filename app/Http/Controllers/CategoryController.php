<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

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

        if (in_array($status, ['active', 'inactive'], true)) {
            $categoriesQuery->where('status', $status);
        }

        $categories = $categoriesQuery->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'q' => $q,
            'status' => $status,
        ]);
    }
}
