<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController extends Controller
{
    /**
     * Handle Google login/registration from Flutter.
     *
     * @unauthenticated
     */
    public function handleGoogleLogin(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'access_token' => ['required', 'string'],
                'device_name' => ['required', 'string'],
            ]);

            // Get user info from Google using the access token
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($validated['access_token']);

            // Find existing user or create new one
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // Update or create social provider record
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

            // Delete existing tokens for this device name
            $user->tokens()->where('name', $validated['device_name'])->delete();

            // Create new token
            $token = $user->createToken(
                name: $validated['device_name'],
                abilities: ['*'],
                expiresAt: now()->addDays(30),
            )->plainTextToken;

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Redirect to Google for authentication.
     *
     * @unauthenticated
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Handle Google callback from Flutter.
     *
     * @unauthenticated
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // Update or create social provider record
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

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => new UserResource($user),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
