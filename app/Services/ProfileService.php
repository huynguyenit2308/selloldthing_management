<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// 👇 1. IMPORT MODEL USER
use App\Models\User;

class ProfileService
{
    /**
     * Hiển thị thông tin cá nhân
     */
    public function show()
    {
        $user = Auth::user();

        // 👇 2. KIỂM TRA BẢO MẬT 2 TAB
        // Nếu user trong session không còn, hoặc không tìm thấy trong DB (do tab kia đã xóa)
        if (!$user || !User::find($user->id)) {
            return $this->forceLogoutAndRedirect();
        }

        // Auto-fill fullname nếu trống
        if (empty($user->fullname) && !empty($user->name)) {
            $user->fullname = $user->name;
        }

        // Kiểm tra xem user đã nhập đủ thông tin chưa
        $missingInfo = empty($user->fullname) || empty($user->phone) || empty($user->address);

        return view('loc.info-personal_v2', compact('user', 'missingInfo'));
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit()
    {
        $user = Auth::user();

        // 👇 2. KIỂM TRA BẢO MẬT 2 TAB
        if (!$user || !User::find($user->id)) {
            return $this->forceLogoutAndRedirect();
        }

        return view('loc.edit-personal_v2', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 👇 2. KIỂM TRA BẢO MẬT 2 TAB
        // Kiểm tra trước khi validate để tránh xử lý dữ liệu thừa
        if (!$user || !User::find($user->id)) {
            return $this->forceLogoutAndRedirect();
        }
        // 2. [LOGIC MỚI - SỬA LẠI] SO SÁNH TIMESTAMP
        $userInDb = User::find($user->id);

        // Lấy timestamp từ Form gửi lên (ép kiểu int)
        $clientTimestamp = (int) $request->input('last_updated_at');

        // Lấy timestamp thực tế trong DB
        $dbTimestamp = $userInDb->updated_at ? $userInDb->updated_at->timestamp : 0;

        // SO SÁNH: Chỉ cần KHÁC NHAU là chặn (nghĩa là DB đã bị thay đổi)
        if ($dbTimestamp != $clientTimestamp) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Dữ liệu đã bị thay đổi bởi phiên làm việc khác. Hệ thống sẽ tải lại thông tin mới nhất.')
                ->with('reload_page', true);
        }
        $request->validate([
            'username' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'phone' => 'nullable|regex:/^[0-9]{9,11}$/',
            'email' => 'required|email',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'username.required' => 'Vui lòng nhập tên người dùng.',
            'fullname.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        // Lấy lại instance mới nhất từ DB để update (an toàn hơn dùng session cũ)
        $userInDb = User::find($user->id);

        // Upload ảnh đại diện nếu có
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $userInDb->avatar = $path;
        }

        // Cập nhật thông tin vào DB
        $userInDb->username = $request->username;
        $userInDb->fullname = $request->fullname;
        $userInDb->phone = $request->phone;
        $userInDb->email = $request->email;
        $userInDb->address = $request->address;
        $userInDb->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * 👇 3. HÀM PHỤ TRỢ XỬ LÝ LỖI ĐỒNG BỘ TAB
     */
    private function forceLogoutAndRedirect()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Tài khoản không xác định hoặc đã thay đổi. Vui lòng tải lại trang.');
    }
}