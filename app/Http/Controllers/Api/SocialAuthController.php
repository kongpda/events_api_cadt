<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\GoogleAuthService;
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
    public function handleGoogleLogin(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'access_token' => ['required', 'string'],
                'device_name' => ['required', 'string'],
            ]);

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($validated['access_token']);

            $user = $this->googleAuthService->findOrCreateUser($googleUser);

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
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = $this->googleAuthService->findOrCreateUser($googleUser);

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
