<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as GoogleUser;

final class GoogleAuthService
{
    public function findOrCreateUser(GoogleUser $googleUser): User
    {
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(16)),
            ]
        );

        $this->updateSocialProvider($user, $googleUser);

        return $user;
    }

    private function updateSocialProvider(User $user, GoogleUser $googleUser): void
    {
        $user->socialProviders()->updateOrCreate(
            [
                'provider_slug' => 'google',
                'provider_user_id' => $googleUser->getId(),
            ],
            [
                'nickname' => $googleUser->getNickname(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'provider_data' => $googleUser->getRaw(),
                'token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
                'token_expires_at' => $googleUser->expiresIn
                    ? now()->addSeconds($googleUser->expiresIn)
                    : null,
            ]
        );
    }
}
