<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountService
{
    public function info()
    {
        $user = Auth::user();
        return view('loc.info-acc', compact('user'));
    }

    public function showChangePassword()
    {
        return view('loc.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
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

        $user = Auth::user();

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
        $user = Auth::user();
        $user->delete();

        Auth::logout();
        return redirect('/')->with('success', 'Tài khoản đã được xóa.');
    }
}
