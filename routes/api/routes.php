<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventParticipantController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth:sanctum'])->get('/user', fn (Request $request) => $request->user());

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
