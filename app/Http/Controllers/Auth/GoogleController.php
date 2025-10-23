<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // public function callback()
    // {
    //     $googleUser = Socialite::driver('google')->user();

    //     // Kiểm tra user có trong DB chưa
    //     $user = User::where('email', $googleUser->getEmail())->first();

    //     if (!$user) {
    //         // Nếu chưa có thì tạo mới
    //         $user = User::create([
    //             'name' => $googleUser->getName(),
    //             'email' => $googleUser->getEmail(),
    //             'password' => bcrypt(str()->random(16)), // random pass
    //         ]);
    //     }

    //     // Đăng nhập luôn
    //     Auth::login($user);

    //     return redirect()->route('home')->with('success', 'Đăng nhập bằng Google thành công!');
    // }
    public function callback()
{
    $googleUser = Socialite::driver('google')->stateless()->user();

    $user = User::where('email', $googleUser->getEmail())->first();

    if (!$user) {
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'password' => bcrypt(str()->random(16)),
        ]);
    }

    Auth::login($user);

    return redirect()->route('home')->with('success', 'Đăng nhập bằng Google thành công!');
}

}
