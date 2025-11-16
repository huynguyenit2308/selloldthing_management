<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Rules\NoFullWidthSpace;
use App\Rules\NotEmptyOrSpace;
use App\Rules\HasAtLeastOneChar;
use App\Rules\NoHTML;

class AuthService
{
    // ======= LOGIN =======
    public function handleLogin(Request $request)
    {
        $request->validate([
                new NoFullWidthSpace(),
                new NotEmptyOrSpace(),
                new HasAtLeastOneChar(),
                new NoHTML(),
            // Bổ sung max:255 để nhất quán với CSDL
            'email' => 'required|email|max:20',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email' => 'Email không hợp lệ',
            'email.max' => 'Email không được vượt quá 255 ký tự', // Bổ sung
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
                'confirmed',
            ],
            // Tinh chỉnh validation cho SĐT Việt Nam (bắt đầu bằng 0, 10 số)
            'phone' => [
                'required',
                'string',
                'regex:/^0[0-9]{9}$/'
            ],
        ], [
            // Bổ sung các lỗi còn thiếu cho email
            'email.required' => 'Vui lòng nhập Email.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',

            // Bổ sung các lỗi còn thiếu cho password
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

            // Bổ sung các lỗi còn thiếu cho phone
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ (Phải bắt đầu bằng 0 và có 10 chữ số).',
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