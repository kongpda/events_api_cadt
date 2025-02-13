<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Two\User as SocialiteUser;

final class GoogleAuthService
{
    public function findOrCreateUser(SocialiteUser $googleUser, array $userData): User
    {
        return DB::transaction(function () use ($googleUser, $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email_verified_at' => now(),
                ]
            );

            $user->socialProviders()->updateOrCreate(
                [
                    'provider_slug' => 'google',
                    'provider_user_id' => $userData['provider_id'],
                ],
                [
                    'nickname' => $googleUser->getNickname(),
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'avatar' => $userData['photo_url'] ?? null,
                    'provider_data' => $googleUser->getRaw(),
                    'token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => $googleUser->expiresIn
                        ? now()->addSeconds($googleUser->expiresIn)
                        : null,
                ]
            );

            return $user;
        });
    }
}
