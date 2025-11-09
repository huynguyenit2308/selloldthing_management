<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Services\SocialAccountService;
use Exception;

class FacebookController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback(SocialAccountService $service)
    {
        try {
            $providerUser = Socialite::driver('facebook')->user();
            $service->createOrGetUser($providerUser, 'facebook');
            return redirect()->route('home');
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Đăng nhập Facebook thất bại!');
        }
    }
}
