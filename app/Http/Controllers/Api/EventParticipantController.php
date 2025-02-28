<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventParticipant\ToggleEventParticipantRequest;
use App\Http\Resources\EventParticipantResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventParticipant;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /**
     * Toggle participation status for an event
     *
     * Toggle whether the authenticated user is participating in the specified event.
     */
    public function toggle(Event $event, ToggleEventParticipantRequest $request): JsonResponse
    {
        $user = Auth::user();

        if ( ! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $participant = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant) {
            $participant->delete();
            $isParticipating = false;
            $message = 'You are no longer participating in this event';
        } else {
            // Get validated data from request with defaults
            $validated = $request->validated();
            $status = $validated['status'] ?? ParticipationStatus::REGISTERED;
            $participationType = $validated['participation_type'] ?? ParticipationType::PAID;
            $ticketTypeId = $validated['ticket_type_id'] ?? null;

            // Use a transaction to ensure atomicity when checking capacity and creating participant
            try {
                DB::beginTransaction();

                EventParticipant::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => $status,
                    'participation_type' => $participationType,
                    'ticket_type_id' => $ticketTypeId,
                    'joined_at' => now(),
                ]);

                DB::commit();
                $isParticipating = true;
                $message = 'You are now participating in this event';
            } catch (Exception $e) {
                DB::rollBack();
                ray($e);

                return response()->json([
                    'message' => 'An error occurred while registering for the event',
                    'is_participating' => false,
                ], 500);
            }
        }

        return response()->json([
            'message' => $message,
            'is_participating' => $isParticipating,
        ]);
    }
}
