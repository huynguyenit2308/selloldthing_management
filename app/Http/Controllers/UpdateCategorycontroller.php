<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UpdateCategoryController extends Controller
{
    public function edit(int $categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_NOT_FOUND: Danh mục không tồn tại hoặc đã bị xóa']);
        }

        return view('admin.categories.update_category', compact('category'));
    }

    public function update(Request $request, int $categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_NOT_FOUND: Danh mục không tồn tại hoặc đã bị xóa']);
        }

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                    'regex:/^[\pL\pN\s\.,\-_\/()]+$/u',
                    Rule::unique('categories', 'name')->ignore($category->id),
                ],
                'description' => ['nullable', 'string', 'max:500'],
                'image' => ['nullable', 'image', 'max:2048'],
                'status' => ['required', Rule::in(['0', '1', 0, 1])],
            ],
            [
                'name.required' => 'CATEGORY_NAME_REQUIRED: Tên danh mục không được để trống',
                'name.min' => 'CATEGORY_NAME_REQUIRED: Tên danh mục không được để trống',
                'name.max' => 'CATEGORY_NAME_TOO_LONG: Tên danh mục không được vượt quá 255 ký tự',
                'name.regex' => 'CATEGORY_NAME_INVALID: Tên danh mục chứa ký tự không hợp lệ',
                'name.unique' => 'CATEGORY_NAME_EXISTS: Tên danh mục đã tồn tại trong hệ thống',
                'image.image' => 'IMAGE_FORMAT_INVALID: Định dạng file không được hỗ trợ',
                'image.max' => 'IMAGE_SIZE_EXCEEDED: Kích thước file không được vượt quá 2MB',
                'status.required' => 'CATEGORY_STATUS_REQUIRED: Trạng thái danh mục là bắt buộc',
                'status.in' => 'CATEGORY_STATUS_INVALID: Trạng thái danh mục không hợp lệ',
            ]
        );

        $imagePath = $category->image;

        if ($request->hasFile('image')) {
            try {
                $newImagePath = $request->file('image')->store('categories', 'public');

                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }

                $imagePath = $newImagePath;
            } catch (Exception $e) {
                Log::error('IMAGE_UPLOAD_FAILED: ' . $e->getMessage());

                return back()
                    ->withInput()
                    ->withErrors(['image' => 'IMAGE_UPLOAD_FAILED: Không thể upload hình ảnh']);
            }
        }

        try {
            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'status' => (int) $validated['status'],
            ]);
        } catch (Exception $e) {
            Log::error('SYSTEM_ERROR: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['system' => 'SYSTEM_ERROR: Đã có lỗi xảy ra, vui lòng thử lại sau']);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Danh mục đã được cập nhật thành công');
    }
}
