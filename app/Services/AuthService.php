<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{
    // ======= LOGIN =======
    public function handleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email' => 'Email không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        session(['is_new_user' => true]);
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return [
                'success' => true,
                'redirect' => route('home'),
                'message' => 'Đăng nhập thành công!',
            ];
        }

        return [
            'success' => false,
            'message' => 'Email hoặc mật khẩu không đúng!',
            'old_input' => $request->only('email'),
        ];
    }

    // ======= REGISTER =======
    public function handleRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // thêm xác nhận mật khẩu
            ],
            'phone' => 'required|regex:/^[0-9]+$/|digits_between:10,11',
        ], [
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        User::create([
            'name' => $request->email,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        return [
            'success' => true,
            'redirect' => route('login'),
            'message' => 'Đăng ký thành công! Vui lòng đăng nhập.',
        ];
    }

    // ======= LOGOUT =======
    public function handleLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return [
            'success' => true,
            'redirect' => route('login'),
        ];
    }
}
