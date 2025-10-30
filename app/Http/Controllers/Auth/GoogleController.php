<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Services\SocialAccountService;
use Exception;

class GoogleController extends Controller
{
    protected $service;

    public function __construct(SocialAccountService $service)
    {
        $this->service = $service;
    }

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

   public function callback()
{
    try {
        $providerUser = Socialite::driver('google')->stateless()->user();
        app(\App\Services\SocialAccountService::class)->createOrGetUser($providerUser, 'google');
        return redirect()->route('home')->with('success', 'Đăng nhập Google thành công!');
    } catch (\Exception $e) {
        return redirect()->route('login')->with('error', 'Đăng nhập thất bại!');
    }
}

}
