<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class EventFavoriteController extends Controller
{
    /**
     * List user's favorite events
     */
    public function index(): AnonymousResourceCollection
    {
        $favorites = auth()->user()
            ->favoriteEvents()
            ->with(['category', 'user', 'organizer', 'tags', 'favorites'])
            ->withCount('favorites')
            ->when(auth()->check(), function ($query): void {
                $query->withExists(['favorites as is_favorited' => function ($query): void {
                    $query->where('user_id', auth()->id());
                }]);
            })
            ->latest()
            ->paginate();

        return EventResource::collection($favorites);
    }

    /**
     * Toggle favorite status for an event
     */
    public function toggle(Event $event): JsonResponse
    {
        auth()->user()->favoriteEvents()->toggle($event->id);

        $isFavorited = auth()->user()->favoriteEvents()
            ->where('event_id', $event->id)
            ->exists();

        return response()->json([
            'message' => $isFavorited
                ? 'Event added to favorites'
                : 'Event removed from favorites',
            'is_favorited' => $isFavorited,
        ]);
    }
}
