<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AddCategoryController extends Controller
{
    public function create()
    {
        return view('admin.categories.add_category');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                    'regex:/^[\pL\pN\s\.,\-_\/()]+$/u',
                    Rule::unique('categories', 'name'),
                ],
                'description' => ['nullable', 'string', 'max:500'],
                'image' => ['nullable', 'image', 'max:2048'],
            ],
            [
                'name.required' => 'CATEGORY_NAME_REQUIRED: Tên danh mục không được để trống',
                'name.min' => 'CATEGORY_NAME_REQUIRED: Tên danh mục không được để trống',
                'name.max' => 'CATEGORY_NAME_TOO_LONG: Tên danh mục không được vượt quá 255 ký tự',
                'name.regex' => 'CATEGORY_NAME_INVALID: Tên danh mục chứa ký tự không hợp lệ',
                'name.unique' => 'CATEGORY_NAME_EXISTS: Tên danh mục đã tồn tại trong hệ thống',
                'image.image' => 'IMAGE_FORMAT_INVALID: Định dạng file không được hỗ trợ',
                'image.max' => 'IMAGE_SIZE_EXCEEDED: Kích thước file không được vượt quá 2MB',
            ]
        );

        $imagePath = null;

        if ($request->hasFile('image')) {
            try {
                $imagePath = $request->file('image')->store('categories', 'public');
            } catch (Exception $e) {
                Log::error('IMAGE_UPLOAD_FAILED: ' . $e->getMessage());

                return back()
                    ->withInput()
                    ->with('image_upload_error', 'IMAGE_UPLOAD_FAILED: Không thể upload hình ảnh');
            }
        }

        try {
            Category::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'status' => 1,
            ]);
        } catch (Exception $e) {
            Log::error('SYSTEM_ERROR: ' . $e->getMessage());

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return back()
                ->withErrors(['system' => 'SYSTEM_ERROR: Đã có lỗi xảy ra, vui lòng thử lại sau'])
                ->withInput();
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Danh mục đã được tạo thành công');
    }
}
