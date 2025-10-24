<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class FacebookController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->scopes(['email'])->redirect();
    }

    public function callback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // Tìm user theo facebook_id hoặc email
            $user = User::where('facebook_id', $facebookUser->getId())
                        ->orWhere('email', $facebookUser->getEmail())
                        ->first();

            if (!$user) {
                $user = User::create([
                    'name'        => $facebookUser->getName(),
                    'email'       => $facebookUser->getEmail(),
                    'facebook_id' => $facebookUser->getId(),
                    'password'    => bcrypt('12345678'), // tạm
                ]);
            } else {
                // Nếu có user rồi nhưng chưa lưu facebook_id thì update
                if (!$user->facebook_id) {
                    $user->update([
                        'facebook_id' => $facebookUser->getId(),
                    ]);
                }
            }

            // Login user
            Auth::login($user);

            return redirect()->route('home');
        } catch (Exception $e) {
            // Nếu có lỗi -> quay lại login với message
            return redirect()->route('login.form')->with('error', 'Đăng nhập Facebook thất bại!');
        }
    }
}
