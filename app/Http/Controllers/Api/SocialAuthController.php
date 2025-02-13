<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleAuthRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\GoogleAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController extends Controller
{
    public function __construct(
        private readonly GoogleAuthService $googleAuthService
    ) {}

    /**
     * Handle Google login/registration from Flutter.
     *
     * @unauthenticated
     */
    public function handleGoogleLogin(GoogleAuthRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($validated['access_token']);

            if ($googleUser->getEmail() !== $validated['email']) {
                throw new Exception('Email verification failed');
            }

            $user = $this->googleAuthService->findOrCreateUser($googleUser, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'photo_url' => $validated['photo_url'] ?? null,
                'provider_id' => $validated['provider_id'],
            ]);

            // Delete existing tokens for this device name
            $user->tokens()->where('name', $validated['device_name'])->delete();

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
    public function redirectToGoogle(): JsonResponse
    {
        try {
            $url = Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json(['url' => $url]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to generate Google auth URL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Google callback.
     *
     * @unauthenticated
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            if ($request->has('error')) {
                throw new Exception($request->error_description ?? 'Google authentication failed');
            }

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = $this->googleAuthService->findOrCreateUser($googleUser, [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'photo_url' => $googleUser->getAvatar(),
                'provider_id' => $googleUser->getId(),
            ]);

            $token = $user->createToken(
                name: $request->device_name ?? 'default_device',
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
}
