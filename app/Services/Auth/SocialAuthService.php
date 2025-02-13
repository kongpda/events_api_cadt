<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SocialAuthService
{
    public function findOrCreateUser($socialUser, array $userData, string $provider = 'google'): User
    {
        return DB::transaction(function () use ($socialUser, $userData, $provider) {
            // First try to find user through social provider
            $user = User::whereHas('socialProviders', function ($query) use ($userData, $provider): void {
                $query->where('provider_slug', $provider)
                    ->where('provider_user_id', $userData['provider_user_id']);
            })->first();

            // If not found, try to find by email
            if ( ! $user) {
                $user = User::where('email', $userData['email'])->first();
            }

            // If no user exists, create one
            if ( ! $user) {
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'email_verified_at' => now(), // Since provider has verified the email
                ]);
            }

            // Update or create the social provider entry
            $user->socialProviders()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'provider_slug' => $provider,
                ],
                [
                    'provider_user_id' => $userData['provider_user_id'],
                    'nickname' => $userData['nickname'] ?? null,
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'avatar' => $userData['photo_url'] ?? null,
                    'token' => $userData['access_token'] ?? null,
                    'provider_data' => is_string($socialUser) ? $socialUser : json_encode($socialUser),
                    'token_expires_at' => isset($socialUser->expiresIn)
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]
            );

            return $user;
        });
    }
}
