<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

final class TestAuthController extends Controller
{
    public function showTestPage(): View
    {
        return view('auth.test-google');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

            return view('auth.callback-result', [
                'success' => true,
                'user' => [
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'id' => $user->getId(),
                    'avatar' => $user->getAvatar(),
                    'token' => $user->token,
                ],
            ]);

        } catch (Exception $e) {
            return view('auth.callback-result', [
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
