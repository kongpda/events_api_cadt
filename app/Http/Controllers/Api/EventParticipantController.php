<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventParticipant\StoreEventParticipantRequest;
use App\Http\Requests\EventParticipant\UpdateEventParticipantRequest;
use App\Http\Resources\EventParticipantResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class EventParticipantController extends Controller
{
    /**
     * List event participants
     *
     * Display a listing of the event participants.
     */
    public function index(Event $event): AnonymousResourceCollection
    {
        $participants = $event->participants()
            ->with(['user', 'ticketType'])
            ->paginate();

        return EventParticipantResource::collection($participants);
    }

    /**
     * Create event participant
     *
     * Create a new event participant.
     */
    public function store(StoreEventParticipantRequest $request): EventParticipantResource
    {
        $participant = EventParticipant::create($request->validated());

        return new EventParticipantResource($participant);
    }

    /**
     * Show event participant
     *
     * Display the specified event participant.
     */
    public function show(Event $event, EventParticipant $participant): EventParticipantResource
    {
        $participant->loadMissing(['user', 'ticketType']);

        return new EventParticipantResource($participant);
    }

    public function update(
        UpdateEventParticipantRequest $request,
        Event $event,
        EventParticipant $participant,
    ): EventParticipantResource {
        $participant->update($request->validated());

        $participant->loadMissing(['user', 'ticketType']);

        return new EventParticipantResource($participant);
    }

    /**
     * Delete event participant
     *
     * Delete the specified event participant.
     */
    public function destroy(Event $event, EventParticipant $participant): JsonResponse
    {
        $participant->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }

    /**
     * Get events by user
     *
     * Retrieve all events that a specific user is participating in.
     */
    public function eventsByUser(string $userId): AnonymousResourceCollection
    {
        $events = Event::whereHas('participants', function ($query) use ($userId): void {
            $query->where('user_id', $userId);
        })
            ->withCount('favorites')
            ->with(['participants'])
            ->paginate();

        return EventResource::collection($events);
    }
}
