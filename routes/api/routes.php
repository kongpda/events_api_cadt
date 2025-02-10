<?php

declare(strict_types=1);

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventParticipantController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Route::post('/auth/token', [AuthController::class, 'generateToken']);
Route::post('/auth/login', [AuthController::class, 'generateToken']);

Route::middleware(['auth:sanctum'])->group(function (): void {
    // Protected User API routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Logout route
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
Route::apiResource('categories', CategoryController::class);

Route::apiResource('events', EventController::class);

Route::apiResource('event-participants', EventParticipantController::class);

Route::apiResource('favorites', FavoriteController::class);

Route::apiResource('organizers', OrganizerController::class);

Route::apiResource('shares', ShareController::class);

Route::apiResource('tags', TagController::class);

Route::apiResource('tickets', TicketController::class);

Route::apiResource('ticket-types', TicketTypeController::class);

// Route::apiResource('venues', VenueController::class);

// Route::apiResource('users', UserController::class);

// Route::apiResource('orders', OrderController::class);
