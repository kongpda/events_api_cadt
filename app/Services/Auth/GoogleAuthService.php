<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Two\User as SocialiteUser;

final class GoogleAuthService
{
    public function findOrCreateUser(SocialiteUser $googleUser, array $userData): User
    {
        return DB::transaction(function () use ($googleUser, $userData) {
            // Check if user exists and has a different Google account
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser) {
                $existingProvider = $existingUser->socialProviders()
                    ->where('provider_slug', 'google')
                    ->where('provider_user_id', '!=', $userData['provider_id'])
                    ->first();

                if ($existingProvider) {
                    throw new Exception('This email is associated with a different Google account.');
                }
            }

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
