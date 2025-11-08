<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SocialAccountService
{
    public function createOrGetUser($providerUser, $provider)
    {
        try {
            $email = $providerUser->getEmail() ?? $providerUser->getId().'@'.$provider.'.local';

            $user = User::where('provider_id', $providerUser->getId())
                        ->orWhere('email', $email)
                        ->first();

            if (!$user) {
                $user = User::create([
                    'name'        => $providerUser->getName() ?? 'User_'.$providerUser->getId(),
                    'email'       => $email,
                    'avatar'      => $providerUser->getAvatar(),
                    'provider'    => $provider,
                    'provider_id' => $providerUser->getId(),
                    'password'    => bcrypt('12345678'),
                    
                ]);
            } else {
                if (!$user->provider_id) {
                    $user->update([
                        'provider'    => $provider,
                        'provider_id' => $providerUser->getId(),
                    ]);
                }
            }

            Auth::login($user);
            return $user;

        } catch (\Exception $e) {
            Log::error('Social login failed: '.$e->getMessage());
            return null;
        }
    }
}
