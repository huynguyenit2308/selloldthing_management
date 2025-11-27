<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoFullWidthSpace;
use App\Rules\NotEmptyOrSpace;
use App\Rules\HasAtLeastOneChar;
use App\Rules\NoHTML;
use App\Models\User;
class AccountService
{
   public function info()
    {
        $user = Auth::user();

        // [LOGIC MỚI] Kiểm tra chéo với Database
        // Nếu Tab 1 đã xóa tài khoản, User::find() sẽ trả về null
        if (!$user || !User::find($user->id)) {
            return $this->forceLogoutAndRedirect();
        }

        return view('loc.info-acc', compact('user'));
    }

    // ======= 2. CẬP NHẬT THÔNG TIN (Nếu bạn làm tính năng này) =======
    // Ví dụ: Bạn tạo thêm nút "Lưu thay đổi" để sửa tên/SĐT
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        // [LOGIC MỚI] Kiểm tra bảo mật 2 Tab
        if (!$user || !User::find($user->id)) {
            return $this->forceLogoutAndRedirect();
        }

        // Validate dữ liệu
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^0[0-9]{9}$/',
        ]);

        // Lưu thay đổi (Cần query lại user để chắc chắn update vào instance mới nhất)
        $userInDb = User::find($user->id);
        $userInDb->name = $request->name;
        $userInDb->phone = $request->phone;
        $userInDb->save();

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function showChangePassword()
    {
        // Kiểm tra user tồn tại trước khi hiển thị view
        if (!Auth::check()) {
            return $this->forceLogoutAndRedirect();
        }
        return view('loc.change-password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // 1. KIỂM TRA AN TOÀN: 
        // Nếu user này đã bị xóa ở Tab 1, biến $user sẽ là null.
        if (!$user) {
            return $this->forceLogoutAndRedirect();
        }

        $request->validate([
            new NoFullWidthSpace(),
            new NotEmptyOrSpace(),
            new HasAtLeastOneChar(),
            new NoHTML(),
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.regex' => 'Mật khẩu mới phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng.');
        }

        if (Hash::check($request->new_password, $user->password)) {
            return back()->with('error', 'Mật khẩu mới không được trùng với mật khẩu hiện tại.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('password_changed', true);
    }

    public function confirmLogoutAfterChange()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Bạn đã đổi mật khẩu thành công và được đăng xuất.');
    }

    public function deleteAccount(Request $request)
    {
        // Lấy user từ session hiện tại
        $user = Auth::user();

        // 1. KIỂM TRA SESSION: Nếu session đã bị hủy (do logout tab kia)
        if (!$user) {
            return $this->forceLogoutAndRedirect();
        }

        // 2. KIỂM TRA DATABASE (Quan trọng cho trường hợp 2 tab cùng xóa):
        // Mặc dù session còn, nhưng có thể Database đã bị Tab 1 xóa rồi.
        // Ta thử tìm lại user trong DB theo ID.
        $userInDb = User::find($user->id);

        if (!$userInDb) {
            // Nếu không tìm thấy trong DB -> Coi như tài khoản đã bị xóa/lỗi
            return $this->forceLogoutAndRedirect();
        }

        try {
            // Thực hiện xóa record tìm thấy
            $userInDb->delete();
        } catch (\Exception $e) {
            // Nếu có lỗi bất ngờ khi xóa
            return $this->forceLogoutAndRedirect();
        }

        // Xử lý khi xóa thành công (Tab 1 sẽ vào đây)
        Auth::logout();
        return redirect('/')->with('success', 'Tài khoản đã được xóa.');
    }

    /**
     * Hàm phụ trợ xử lý lỗi và đá về trang login
     */
    private function forceLogoutAndRedirect()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Chuyển hướng về route 'login' kèm thông báo lỗi
        return redirect()->route('login')->with('error', 'Tài khoản không tồn tại hoặc đã bị xóa ở tab khác.');
    }
}