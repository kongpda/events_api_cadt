<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UserProfileService
{
    /**
     * Create initial profile for user
     */
    public function createInitialProfile(User $user, ?array $providerData = null): UserProfile
    {
        $names = $this->extractNames($user, $providerData);

        return UserProfile::create([
            'user_id' => $user->id,
            'first_name' => $names['first_name'],
            'last_name' => $names['last_name'],
            'avatar' => $this->getAvatarFromProvider($providerData),
            'status' => UserStatus::ACTIVE->value,
            'auth_provider' => $providerData['provider'] ?? 'email',
            'auth_provider_id' => $providerData['provider_id'] ?? null,
        ]);
    }

    /**
     * Update profile from social provider data
     */
    public function updateFromProvider(UserProfile $profile, array $providerData): UserProfile
    {
        $names = $this->extractNames($profile->user, $providerData);

        $profile->update([
            'first_name' => $names['first_name'],
            'last_name' => $names['last_name'],
            'avatar' => $this->getAvatarFromProvider($providerData) ?? $profile->avatar,
            'auth_provider' => $providerData['provider'],
            'auth_provider_id' => $providerData['provider_id'],
        ]);

        return $profile;
    }

    /**
     * Update user's avatar
     */
    public function updateAvatar(UserProfile $profile, UploadedFile $avatar): UserProfile
    {
        // Delete old avatar if exists
        if ($profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
        }

        // Generate unique filename
        $filename = sprintf(
            'avatars/%s.%s',
            Str::uuid(),
            $avatar->getClientOriginalExtension()
        );

        // Store new avatar
        $avatar->storeAs('public', $filename);

        // Update profile
        $profile->update(['avatar' => $filename]);

        return $profile;
    }

    /**
     * Delete user's avatar
     */
    public function deleteAvatar(UserProfile $profile): void
    {
        if ($profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
            $profile->update(['avatar' => null]);
        }
    }

    public function createProfile(User $user, array $data): UserProfile
    {
        return $user->profile()->create($data);
    }

    /**
     * List profiles with pagination and optional filters
     */
    public function listProfiles(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null
    ): LengthAwarePaginator {
        return UserProfile::query()
            ->with('user')
            ->when($search, function ($query, $search): void {
                $query->whereHas('user', function ($query) use ($search): void {
                    $query->where('email', 'like', "%{$search}%");
                })
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->when($status, function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Extract first and last names from user data
     */
    private function extractNames(User $user, ?array $providerData = null): array
    {
        if ($providerData && isset($providerData['first_name'])) {
            return [
                'first_name' => $providerData['first_name'],
                'last_name' => $providerData['last_name'] ?? '',
            ];
        }

        $nameParts = explode(' ', $user->name);

        return [
            'first_name' => $nameParts[0] ?? '',
            'last_name' => $nameParts[1] ?? '',
        ];
    }

    /**
     * Get avatar from provider or return null
     */
    private function getAvatarFromProvider(?array $providerData = null): ?string
    {
        if ( ! $providerData || empty($providerData['avatar'])) {
            return null;
        }

        // For providers that give avatar URL
        if (filter_var($providerData['avatar'], FILTER_VALIDATE_URL)) {
            $filename = 'avatars/' . uniqid() . '.jpg';

            // Download and store the avatar
            $response = Http::get($providerData['avatar']);
            if ($response->successful()) {
                Storage::put('public/' . $filename, $response->body());

                return $filename;
            }
        }

        return null;
    }
}
