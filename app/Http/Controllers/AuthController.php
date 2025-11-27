<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ======= LOGIN =======
    public function showLoginForm()
    {
        return view('loc.login');
    }

    public function login(Request $request)
    {
        $result = $this->authService->handleLogin($request);

        if ($result['success']) {
            return redirect($result['redirect'])->with('success', $result['message']);
        }

        return back()->withInput($result['old_input'])->with('error', $result['message']);
    }

    // ======= REGISTER =======
    public function showRegisterForm()
    {
        return view('loc.register');
    }

    public function register(Request $request)
    {
        $result = $this->authService->handleRegister($request);

        if ($result['success']) {
            return redirect($result['redirect'])->with('success', $result['message']);
        }

        return back()->with('error', 'Đăng ký thất bại, vui lòng thử lại.');
    }

    // ======= LOGOUT =======
   public function logout(Request $request)
    {
        $result = $this->authService->handleLogout($request);

        // KIỂM TRA: Nếu Service trả về thất bại (do đã logout ở tab khác)
        if (isset($result['success']) && !$result['success']) {
            // Chuyển hướng về login kèm thông báo lỗi để hiển thị alert đỏ
            return redirect($result['redirect'])->with('error', $result['message']);
        }

        // Trường hợp đăng xuất bình thường
        return redirect($result['redirect']);
    }
}
