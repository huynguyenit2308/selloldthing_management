<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // ======= LOGIN =======
    public function showLoginForm()
    {
        return view('loc.login');
    }

    public function login(Request $request)
    {
        // Validate dữ liệu đầu vào
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
            return redirect()->intended(route('home'))
                ->with('success', 'Đăng nhập thành công!');
        }

        // Nếu thất bại, trả về lỗi
        return back()->withInput($request->only('email'))
                     ->with('error', 'Email hoặc mật khẩu không đúng!');
    }


    // ======= REGISTER =======
    public function showRegisterForm()
    {
        return view('loc.register'); // file resources/views/loc/register.blade.php
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'phone' => 'required|regex:/^[0-9]+$/|digits_between:10,11',
        ]);

        User::create([
            'name' => $request->email, // có thể đổi thành field name riêng
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    }

    // ======= LOGOUT =======
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
