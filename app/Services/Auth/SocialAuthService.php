<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\AuthProvider;
use App\Models\SocialProvider;
use App\Models\User;
use App\Services\UserProfileService;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Two\User as SocialiteUser;

final class SocialAuthService
{
    public function __construct(
        private readonly UserProfileService $profileService
    ) {}

    public function findOrCreateUser(SocialiteUser|string $socialUser, array $userData, string $provider = 'google'): User
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

            // Handle social provider data
            $this->updateSocialProvider($user, $socialUser, $userData, $provider);

            // Handle user profile
            $providerData = [
                'provider' => AuthProvider::from($provider),
                'provider_id' => $userData['provider_user_id'],
                'first_name' => $userData['name'] ?? null,
                'last_name' => null,
                'avatar' => $userData['photo_url'] ?? null,
            ];

            // If we have detailed name information from social provider
            if ($socialUser instanceof SocialiteUser && isset($socialUser->user['given_name'])) {
                $providerData['first_name'] = $socialUser->user['given_name'];
                $providerData['last_name'] = $socialUser->user['family_name'] ?? null;
            }

            if ($user->profile) {
                $this->profileService->updateFromProvider($user->profile, $providerData);
            } else {
                $this->profileService->createInitialProfile($user, $providerData);
            }

            return $user;
        });
    }

    private function updateSocialProvider(User $user, SocialiteUser|string $socialUser, array $userData, string $provider): void
    {
        $socialProviderData = [
            'user_id' => $user->id,
            'provider_slug' => $provider,
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
        ];

        $socialProvider = SocialProvider::where('user_id', $user->id)
            ->where('provider_slug', $provider)
            ->first();

        if ($socialProvider) {
            $socialProvider->update($socialProviderData);
        } else {
            $user->socialProviders()->create($socialProviderData);
        }
    }
}
