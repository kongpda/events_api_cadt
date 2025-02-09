<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventParticipantResource;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Response;

final class EventParticipantController extends Controller
{
    public function index(Event $event): AnonymousResourceCollection
    {
        $participants = $event->participants()
            ->with(['user', 'ticketType'])
            ->paginate();

        return EventParticipantResource::collection($participants);
    }

    public function store(Request $request): EventParticipantResource
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'user_id' => ['required', 'exists:users,id'],
            'status' => ['required', new Enum(ParticipationStatus::class)],
            'participation_type' => ['required', new Enum(ParticipationType::class)],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'check_in_time' => ['nullable', 'date'],
            'joined_at' => ['required', 'date'],
        ]);

        $participant = EventParticipant::create($validated);

        return new EventParticipantResource($participant);
    }

    public function show(Event $event, EventParticipant $participant): EventParticipantResource
    {
        return new EventParticipantResource($participant->load(['user', 'ticketType']));
    }

    public function update(
        Request $request,
        Event $event,
        EventParticipant $participant
    ): EventParticipantResource {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:' . implode(',', [
                ParticipationStatus::REGISTERED->value,
                ParticipationStatus::ATTENDED->value,
                ParticipationStatus::CANCELLED->value,
                ParticipationStatus::WAITLISTED->value,
                ParticipationStatus::DECLINED->value,
            ])],
            'check_in_time' => ['nullable', 'date'],
        ]);

        $participant->update($validated);

        return new EventParticipantResource($participant->load(['user', 'ticketType']));
    }

    public function destroy(Event $event, EventParticipant $participant): JsonResponse
    {
        $participant->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
