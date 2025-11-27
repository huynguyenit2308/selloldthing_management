<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Crypt; // Để mã hóa/giải mã
use Illuminate\Contracts\Encryption\DecryptException; // Để bắt lỗi khi user sửa bậy

class ProfileService
{
    /**
     * Hiển thị thông tin cá nhân
     */
    public function show()
    {
        $user = Auth::user();

        // 👇 2. KIỂM TRA BẢO MẬT 2 TAB
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

        // Lưu ý: Việc mã hóa token sẽ được thực hiện trực tiếp bên View (Blade)
        // bằng lệnh Crypt::encryptString($user->updated_at->timestamp)
        return view('loc.edit-personal_v2', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
  public function update(Request $request)
    {
        $user = Auth::user();

        // 1. KIỂM TRA TÀI KHOẢN TỒN TẠI
        if (!$user || !User::find($user->id)) {
            return $this->forceLogoutAndRedirect();
        }

        $userInDb = User::find($user->id);

        // =================================================================
        // 2. CHỐNG F12 SỬA TOKEN & CHỐNG 2 TAB
        // =================================================================
        try {
            $encryptedToken = $request->input('secure_version_token');
            
            // Nếu không gửi token lên (do F12 xóa input) -> Lỗi
            if (empty($encryptedToken)) {
                throw new DecryptException('Token is missing');
            }

            // Giải mã. Nếu F12 sửa dù chỉ 1 ký tự -> Ném lỗi DecryptException ngay
            $clientTimestamp = (int) Crypt::decryptString($encryptedToken);

        } catch (DecryptException $e) {
            // 🛑 CHẶN NGAY LẬP TỨC
            return redirect()
                ->route('profile.edit')
                ->with('error', 'CẢNH BÁO: Phát hiện can thiệp dữ liệu bất hợp pháp! Vui lòng không sửa mã nguồn.')
                ->with('reload_page', true);
        }

        // So sánh thời gian
        $dbTimestamp = $userInDb->updated_at ? $userInDb->updated_at->timestamp : 0;

        if ($dbTimestamp != $clientTimestamp) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Dữ liệu đã thay đổi ở nơi khác. Hệ thống sẽ tải lại thông tin mới nhất.')
                ->with('reload_page', true);
        }
        // =================================================================

        $request->validate([
            'username' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'phone' => 'nullable|regex:/^[0-9]{9,11}$/',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // ❌ KHÔNG validate username/email ở đây nữa vì ta không cho sửa
        ], [
            'fullname.required' => 'Vui lòng nhập họ tên.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        // 3. CẬP NHẬT DỮ LIỆU (CHỈ CÁC TRƯỜNG CHO PHÉP)
        
        // Upload ảnh
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $userInDb->avatar = $path;
        }

        // ✅ CHỈ CẬP NHẬT CÁC TRƯỜNG NÀY
        $userInDb->username = $request->username;
        $userInDb->fullname = $request->fullname;
        $userInDb->phone = $request->phone;
        $userInDb->address = $request->address;
        
        // ❌ TUYỆT ĐỐI KHÔNG CẬP NHẬT EMAIL/USERNAME TỪ REQUEST
        // (Đây là lý do F12 sửa email thành công lúc trước)
        // $userInDb->username = $request->username; // BỎ DÒNG NÀY
        // $userInDb->email = $request->email;       // BỎ DÒNG NÀY

        $userInDb->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin thành công!');
    }
    /**
     * Hàm phụ trợ xử lý lỗi đồng bộ tab
     */
    private function forceLogoutAndRedirect()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Tài khoản không xác định hoặc đã thay đổi. Vui lòng tải lại trang.');
    }
}