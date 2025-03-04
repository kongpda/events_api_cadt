<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventFavoriteController;
use App\Http\Controllers\Api\EventParticipantController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/auth/token', [AuthController::class, 'generateToken']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Social Authentication Routes - should be public
Route::prefix('auth')->group(function (): void {
    // Generic social auth endpoint for mobile apps
    Route::post('/social-login', [SocialAuthController::class, 'handleSocialLogin']);

    // Provider specific web routes
    Route::prefix('google')->group(function (): void {
        Route::get('/redirect', [SocialAuthController::class, 'redirectToGoogle']);
        Route::get('/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    });
});

Route::middleware(['auth:sanctum'])->group(function (): void {
    // Protected Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/reset-token', [AuthController::class, 'resetToken']);

    // Protected User routes - only for authenticated users
    // Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

    // profile routes
    Route::get('/user/profile', [UserProfileController::class, 'getCurrentUserProfile'])->name('user.profile');
    Route::apiResource('profiles', UserProfileController::class);
    Route::post('/profile/avatar', [UserProfileController::class, 'updateAvatar']);

    // Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Protected Category routes
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    // Protected Event routes
    Route::get('user/events', [EventController::class, 'userEvents'])->name('user.events');
    Route::get('events/featured', [EventController::class, 'featured'])->name('events.featured');
    Route::get('organizer/events', [EventController::class, 'organizerEvents'])->name('organizer.events');

    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // Protected Event Favorite routes
    Route::get('event-favorites', [EventFavoriteController::class, 'index'])->name('events.favorites');
    Route::post('events/{event}/toggle-favorite', [EventFavoriteController::class, 'toggle'])
        ->name('events.favorite');

    // Protected Event Participant routes (all operations)
    Route::get('events/{event}/participants', [EventParticipantController::class, 'index'])
        ->name('events.participants.index');
    Route::post('events/{event}/toggle-participation', [EventParticipantController::class, 'toggle'])
        ->name('events.participants.toggle');
    Route::get('/users/{userId}/events', [EventParticipantController::class, 'eventsByUser'])
        ->name('events.participants.user');

    // Protected Organizer routes (all operations)
    Route::apiResource('organizers', OrganizerController::class)->names([
        'index' => 'organizers.index',
        'show' => 'organizers.show',
        'store' => 'organizers.store',
        'update' => 'organizers.update',
        'destroy' => 'organizers.destroy',
    ]);

    // Protected Share routes (all operations)
    Route::apiResource('shares', ShareController::class)->names([
        'index' => 'shares.index',
        'show' => 'shares.show',
    ]);

    // Protected Tag routes (all operations)
    Route::apiResource('tags', TagController::class)->names([
        'index' => 'tags.index',
        'show' => 'tags.show',
        'store' => 'tags.store',
        'update' => 'tags.update',
        'destroy' => 'tags.destroy',
    ]);

    // Protected Ticket routes (all operations)
    Route::apiResource('tickets', TicketController::class)->names([
        'index' => 'tickets.index',
        'show' => 'tickets.show',
        'store' => 'tickets.store',
        'update' => 'tickets.update',
        'destroy' => 'tickets.destroy',
    ]);
    Route::get('tickets/{id}/qr-code', [TicketController::class, 'getQrCodeData'])
        ->name('tickets.qr-code');

    // Protected Ticket Type routes (all operations)
    Route::apiResource('ticket-types', TicketTypeController::class)->names([
        'index' => 'ticket_types.index',
        'show' => 'ticket_types.show',
        'store' => 'ticket_types.store',
        'update' => 'ticket_types.update',
        'destroy' => 'ticket_types.destroy',
    ]);

});
