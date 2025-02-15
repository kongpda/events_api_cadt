<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\AuthProvider;
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
use Laravel\Socialite\Two\User as SocialiteUser;

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
            $provider = AuthProvider::from($validated['provider']);

            $socialUser = $this->getSocialUser($provider->value, $validated['access_token']);
            $this->validateSocialEmail($socialUser->getEmail(), $validated['email']);

            $userData = $this->prepareSocialUserData($socialUser, $validated);
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $userData, $provider->value);

            return $this->generateAuthResponse($user, $validated['device_name']);

        } catch (Exception $e) {
            Log::error('Social auth failed', [
                'error' => $e->getMessage(),
                'provider' => $request->provider ?? 'unknown',
                'email' => $request->email ?? 'unknown',
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
            $url = Socialite::driver(AuthProvider::GOOGLE->value)
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json(['url' => $url]);
        } catch (Exception $e) {
            Log::error('Failed to generate Google auth URL', [
                'error' => $e->getMessage(),
            ]);

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

            $socialUser = $this->getSocialUser(AuthProvider::GOOGLE->value);
            $userData = $this->prepareSocialUserData($socialUser);

            $user = $this->socialAuthService->findOrCreateUser(
                $socialUser,
                $userData,
                AuthProvider::GOOGLE->value
            );

            return $this->generateAuthResponse(
                $user,
                $request->device_name ?? 'default_device'
            );

        } catch (Exception $e) {
            Log::error('Google auth callback failed', [
                'error' => $e->getMessage(),
                'code' => $request->code ?? 'no_code',
            ]);

            return $this->handleAuthError($e);
        }
    }

    /**
     * Get social user from provider
     */
    private function getSocialUser(string $provider, ?string $token = null): SocialiteUser
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

    /**
     * Prepare user data from social provider
     */
    private function prepareSocialUserData(SocialiteUser $socialUser, ?array $validated = null): array
    {
        return [
            'name' => $validated['name'] ?? $socialUser->getName(),
            'email' => $validated['email'] ?? $socialUser->getEmail(),
            'photo_url' => $validated['photo_url'] ?? $socialUser->getAvatar(),
            'provider_user_id' => $validated['provider_user_id'] ?? $socialUser->getId(),
            'access_token' => $validated['access_token'] ?? $socialUser->token,
            'nickname' => $socialUser->getNickname(),
            'provider_data' => [
                'given_name' => $socialUser->user['given_name'] ?? null,
                'family_name' => $socialUser->user['family_name'] ?? null,
            ],
        ];
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
                'user' => new UserResource($user->load('profile')),
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
        $statusCode = $e instanceof SocialAuthException ? 401 : 500;

        return response()->json([
            'message' => 'Authentication failed',
            'error' => $e->getMessage(),
        ], $statusCode);
    }
}
