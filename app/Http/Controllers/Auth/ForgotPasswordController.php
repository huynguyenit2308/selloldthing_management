<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // B1: Hiển thị form quên mật khẩu
    public function showForgotForm()
    {
        return view('loc.forgot-password');
    }

    // B2: Gửi mã 6 số qua email
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Email không tồn tại!');
        }

        $code = rand(100000, 999999);
        $user->reset_code = $code;
        $user->reset_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Gửi email
        Mail::raw("Mã xác nhận đặt lại mật khẩu của bạn là: $code (hết hạn sau 10 phút)", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Mã xác nhận khôi phục mật khẩu');
        });

        session(['reset_email' => $user->email]);
        return redirect()->route('password.verifyForm')->with('success', 'Đã gửi mã xác nhận qua email.');
    }

    // B3: Form nhập mã xác nhận
    public function showVerifyForm()
    {
        return view('loc.verify-code');
    }

    // B4: Kiểm tra mã xác nhận
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required']);
        $user = User::where('email', session('reset_email'))->first();

        if (!$user || $user->reset_code !== $request->code || $user->reset_expires_at < now()) {
            return back()->with('error', 'Mã xác nhận không đúng hoặc đã hết hạn.');
        }

        session(['verified_email' => $user->email]);
        return redirect()->route('password.resetForm');
    }

    // B5: Form đổi mật khẩu mới
    public function showResetForm()
    {
        return view('loc.reset-password');
    }

    // B6: Cập nhật mật khẩu mới
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', session('verified_email'))->first();

        if (!$user) {
            return redirect()->route('password.forgot')->with('error', 'Phiên khôi phục không hợp lệ.');
        }

        $user->password = Hash::make($request->password);
        $user->reset_code = null;
        $user->reset_expires_at = null;
        $user->save();

        session()->forget(['reset_email', 'verified_email']);
        return redirect()->route('login.form')->with('success', 'Đổi mật khẩu thành công! Hãy đăng nhập lại.');
    }
}
