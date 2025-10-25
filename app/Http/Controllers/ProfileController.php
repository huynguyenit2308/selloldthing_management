<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Hiển thị thông tin cá nhân
     */
    public function show()
    {
        $user = Auth::user();

        // Auto-fill fullname nếu trống
        if (empty($user->fullname) && !empty($user->name)) {
            $user->fullname = $user->name;
        }

        // ✅ Kiểm tra xem user đã nhập đủ thông tin chưa
        $missingInfo = empty($user->fullname) || empty($user->phone) || empty($user->address);

        return view('loc.info-personal', compact('user', 'missingInfo'));
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit()
    {
        $user = Auth::user();
        return view('loc.edit-personal', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
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

        $user = Auth::user();

        // Upload ảnh đại diện nếu có
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Cập nhật thông tin
        $user->username = $request->username;
        $user->fullname = $request->fullname;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin thành công!');
    }
}
