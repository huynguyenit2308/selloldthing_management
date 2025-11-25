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
    public function edit($categoryId)
    {
        // [TEST CASE 3] Validate ID
        if (!is_numeric($categoryId) || $categoryId <= 0) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_ID_INVALID: ID danh mục không hợp lệ']);
        }

        // Kiểm tra quyền admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền sửa danh mục');
        }

        $category = Category::find($categoryId);

        if (!$category) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_NOT_FOUND: Danh mục không tồn tại hoặc đã bị xóa']);
        }

        return view('admin.categories.update_category', compact('category'));
    }

    public function update(Request $request, $categoryId)
    {
        // [TEST CASE 3] Validate ID
        if (!is_numeric($categoryId) || $categoryId <= 0) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_ID_INVALID: ID danh mục không hợp lệ']);
        }

        // Kiểm tra quyền admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền sửa danh mục');
        }

        $category = Category::find($categoryId);

        if (!$category) {
            return redirect()
                ->route('admin.categories.index')
                ->withErrors(['system' => 'CATEGORY_NOT_FOUND: Danh mục không tồn tại hoặc đã bị xóa']);
        }

        // [TEST CASE 2] Kiểm tra cập nhật từ tab cũ (optimistic locking)
        if ($request->has('updated_at') && $request->updated_at != $category->updated_at->toString()) {
            return back()
                ->withInput()
                ->withErrors(['system' => 'DATA_OUTDATED: Dữ liệu danh mục đã được thay đổi bởi người khác. Vui lòng tải lại trang để xem dữ liệu mới nhất trước khi cập nhật.']);
        }

        // [TEST CASE 6] Trim & reject whitespace-only (bao gồm cả khoảng trắng 2 bytes)
        $request->merge([
            'name'        => $this->trimAllWhitespace($request->name ?? ''),
            'description' => $this->trimAllWhitespace($request->description ?? ''),
        ]);

        if ($request->name === '' || $this->isOnlyWhitespace($request->name)) {
            return back()->withInput()->withErrors(['name' => 'CATEGORY_NAME_INVALID: Tên danh mục không được toàn khoảng trắng']);
        }

        // [TEST CASE 7] Convert full-width number → normal
        $request->merge([
            'name' => $this->convertFullWidth($request->name),
        ]);

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

        // [TEST CASE 13] Giữ ảnh cũ nếu không upload mới
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

        // [TEST CASE 9] Chống spam nút lưu
        if (session()->has('category_update_lock')) {
            return back()->withErrors(['system' => 'ACTION_TOO_FAST: Bạn thao tác quá nhanh, vui lòng thử lại']);
        }
        session()->put('category_update_lock', true);
        session()->save();

        try {
            $category->updateCategory($validated, $imagePath);
        } catch (Exception $e) {
            Log::error('SYSTEM_ERROR: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['system' => 'SYSTEM_ERROR: Đã có lỗi xảy ra, vui lòng thử lại sau']);
        }

        session()->forget('category_update_lock');

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Danh mục đã được cập nhật thành công');
    }

    // [TEST CASE 7] Hàm chuyển số full-width về half-width
    private function convertFullWidth($str)
    {
        return mb_convert_kana($str, 'n', 'UTF-8');
    }

    // [TEST CASE 6] Hàm trim tất cả các loại khoảng trắng (bao gồm cả 2 bytes)
    private function trimAllWhitespace($str)
    {
        if ($str === '') {
            return '';
        }
        
        // Trim các khoảng trắng thông thường
        $str = trim($str);
        
        // Loại bỏ các khoảng trắng full-width (2 bytes) ở đầu và cuối
        $str = preg_replace('/^[\s　]+|[\s　]+$$/u', '', $str);
        
        return $str;
    }

    // [TEST CASE 6] Hàm kiểm tra chuỗi chỉ chứa khoảng trắng
    private function isOnlyWhitespace($str)
    {
        if ($str === '') {
            return false;
        }
        
        // Kiểm tra sau khi loại bỏ tất cả các loại khoảng trắng có còn nội dung không
        $cleaned = preg_replace('/[\s　]+/u', '', $str);
        return $cleaned === '';
    }
}
