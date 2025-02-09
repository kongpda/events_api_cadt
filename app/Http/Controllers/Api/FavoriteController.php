<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class FavoriteController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $favorites = auth()->user()->favorites()->with('event')->get();

        return FavoriteResource::collection($favorites);
    }

    public function store(Event $event): FavoriteResource
    {
        $favorite = auth()->user()->favorites()->create([
            'event_id' => $event->id,
        ]);

        return new FavoriteResource($favorite->load('event'));
    }

    public function destroy(Event $event): JsonResponse
    {
        auth()->user()->favorites()->where('event_id', $event->id)->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
