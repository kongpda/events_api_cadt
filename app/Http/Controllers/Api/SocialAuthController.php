<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\SocialAuthException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialAuthRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\SocialAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $socialAuthService
    ) {}

    /**
     * Handle social login/registration from mobile app.
     *
     * @unauthenticated
     */
    public function handleSocialLogin(SocialAuthRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $socialUser = $this->getSocialUser($validated['provider'], $validated['access_token']);

            $this->validateSocialEmail($socialUser->getEmail(), $validated['email']);

            $user = $this->socialAuthService->findOrCreateUser($socialUser, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'photo_url' => $validated['photo_url'] ?? null,
                'provider_user_id' => $validated['provider_user_id'],
                'access_token' => $validated['access_token'],
            ], $validated['provider']);

            return $this->generateAuthResponse($user, $validated['device_name']);

        } catch (Exception $e) {
            Log::error('Social auth failed', [
                'error' => $e->getMessage(),
                'provider' => $request->provider ?? 'unknown',
            ]);

            return $this->handleAuthError($e);
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
                throw new SocialAuthException($request->error_description ?? 'Google authentication failed');
            }

            $googleUser = $this->getSocialUser('google');

            $user = $this->socialAuthService->findOrCreateUser($googleUser, [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'photo_url' => $googleUser->getAvatar(),
                'provider_user_id' => $googleUser->getId(),
            ]);

            return $this->generateAuthResponse($user, $request->device_name ?? 'default_device');

        } catch (Exception $e) {
            Log::error('Google auth callback failed', ['error' => $e->getMessage()]);

            return $this->handleAuthError($e);
        }
    }

    private function getSocialUser(string $provider, ?string $token = null): \Laravel\Socialite\Two\User
    {
        $socialite = Socialite::driver($provider)->stateless();

        $socialUser = $token
            ? $socialite->userFromToken($token)
            : $socialite->user();

        if ( ! $socialUser->getId()) {
            throw new SocialAuthException('Unable to retrieve provider user ID');
        }

        return $socialUser;
    }

    private function validateSocialEmail(?string $socialEmail, string $providedEmail): void
    {
        if ($socialEmail !== $providedEmail) {
            throw new SocialAuthException('Email verification failed: emails do not match');
        }
    }

    private function generateAuthResponse($user, string $deviceName): JsonResponse
    {
        try {
            // Delete existing tokens for this device name
            $user->tokens()->where('name', $deviceName)->delete();

            $token = $user->createToken(
                name: $deviceName,
                abilities: ['*'],
                expiresAt: now()->addDays(30),
            )->plainTextToken;

            return response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate auth response', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);
            throw new SocialAuthException('Failed to create authentication token');
        }
    }

    private function handleAuthError(Exception $e): JsonResponse
    {
        return response()->json([
            'message' => 'Authentication failed',
            'error' => $e->getMessage(),
        ], 401);
    }
}
