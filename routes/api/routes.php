<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventFavoriteController;
use App\Http\Controllers\Api\EventParticipantController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/auth/token', [AuthController::class, 'generateToken']);
Route::post('/auth/register', [AuthController::class, 'register']);
// Route::post('/users', [UserController::class, 'store']); // Allow public registration

Route::middleware(['auth:sanctum'])->group(function (): void {
    // Protected Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/reset-token', [AuthController::class, 'resetToken']);

    // Protected User routes - only for authenticated users
    // Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    // Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Protected Category routes
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    // Protected Event routes
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);

    // Protected Event Favorite routes
    Route::get('event-favorites', [EventFavoriteController::class, 'index']);
    Route::post('events/{event}/toggle-favorite', [EventFavoriteController::class, 'toggle'])
        ->name('events.favorite');

    // Protected Event Participant routes (all operations)
    Route::apiResource('event-participants', EventParticipantController::class);

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

    // Protected Ticket Type routes (all operations)
    Route::apiResource('ticket-types', TicketTypeController::class)->names([
        'index' => 'ticket_types.index',
        'show' => 'ticket_types.show',
        'store' => 'ticket_types.store',
        'update' => 'ticket_types.update',
        'destroy' => 'ticket_types.destroy',
    ]);
});
