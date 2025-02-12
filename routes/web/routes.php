<?php

declare(strict_types=1);

use App\Http\Controllers\TestAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): array => [
    'Laravel' => app()->version(),
]);

// Route::get('/test-google-auth', [TestAuthController::class, 'showTestPage'])->name('test.google');
// Route::get('/auth/google/test', [TestAuthController::class, 'redirectToGoogle'])->name('test.google.redirect');
// Route::get('/auth/google/test-callback', [TestAuthController::class, 'handleGoogleCallback'])->name('test.google.callback');
