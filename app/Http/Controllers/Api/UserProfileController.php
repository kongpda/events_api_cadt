<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreProfileRequest;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Models\UserProfile;
use App\Services\UserProfileService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $profileService
    ) {}

    /**
     * List user profiles with pagination and optional filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $profiles = $this->profileService->listProfiles(
                (int) $request->query('per_page', 15),
                $request->query('search'),
                $request->query('status')
            );

            return response()->json([
                'data' => ProfileResource::collection($profiles),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch profiles', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to fetch profiles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user profile details
     */
    public function show(UserProfile $profile): JsonResponse
    {
        try {
            return response()->json([
                'user' => new UserResource($profile->user->load('profile')),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch profile', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
            ]);

            return response()->json([
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request, UserProfile $profile): JsonResponse
    {
        try {
            $updatedProfile = $this->profileService->updateProfile($profile, $request->validated());

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => new UserResource($updatedProfile->user->load('profile')),
            ]);
        } catch (Exception $e) {
            Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
            ]);

            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user's avatar
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $avatar = $request->file('avatar');

            $profile = $this->profileService->updateAvatar($user->profile, $avatar);

            return response()->json([
                'message' => 'Avatar updated successfully',
                'user' => new UserResource($user->load('profile')),
            ]);
        } catch (Exception $e) {
            Log::error('Avatar update failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);

            return response()->json([
                'message' => 'Failed to update avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user profile
     */
    public function destroy(UserProfile $profile): JsonResponse
    {
        try {
            $this->profileService->deleteProfile($profile);

            return response()->json([
                'message' => 'Profile deleted successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Profile deletion failed', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
            ]);

            return response()->json([
                'message' => 'Failed to delete profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new user profile
     */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user already has a profile
            if ($user->profile) {
                return response()->json([
                    'message' => 'User already has a profile',
                ], 422);
            }

            $profile = $this->profileService->createProfile($user, $request->validated());

            return response()->json([
                'message' => 'Profile created successfully',
                'user' => new UserResource($user->load('profile')),
            ], 201);
        } catch (Exception $e) {
            Log::error('Profile creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);

            return response()->json([
                'message' => 'Failed to create profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current authenticated user's profile
     */
    public function getCurrentUserProfile(Request $request): JsonResponse
    {
        try {
            // @phpstan-ignore-next-line
            $user = auth()->user();

            if ( ! $user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $profile = $user->profile;

            if ( ! $profile) {
                return response()->json([
                    'message' => 'Profile not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Profile retrieved successfully',
                'user' => new UserResource($user->load('profile')),
            ]);
        } catch (Exception $e) {
            // @phpstan-ignore-next-line
            Log::error('Failed to fetch user profile', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
