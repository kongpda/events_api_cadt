<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateTicketAction;
use App\Actions\DeleteTicketAction;
use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventParticipant\ToggleEventParticipantRequest;
use App\Http\Resources\EventParticipantResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        // Cast to User model
        $user = User::find($user->id);

        $participant = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        try {
            if ($participant) {
                $participant->delete();

                // Delete tickets when user leaves the event
                app(DeleteTicketAction::class)->execute($event, $user);

                $isParticipating = false;
                $message = 'You are no longer participating in this event';
            } else {
                // Get validated data from request with defaults
                $validated = $request->validated();

                // Create participant record
                $participant = EventParticipant::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => $validated['status'] ?? ParticipationStatus::REGISTERED,
                    'participation_type' => $validated['participation_type'] ?? ParticipationType::FREE,
                    'ticket_type_id' => $validated['ticket_type_id'] ?? null,
                    'joined_at' => now(),
                ]);

                // Create ticket when user joins the event
                app(CreateTicketAction::class)->execute(
                    $event,
                    $user,
                    $validated['ticket_type_id'] ?? null
                );

                $isParticipating = true;
                $message = 'You are now participating in this event';
            }

            return response()->json([
                'message' => $message,
                'is_participating' => $isParticipating,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'The selected ticket type does not exist for this event.',
                'is_participating' => false,
            ], 422);
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Error in toggle participation', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred while processing your request. Please try again later.',
                'is_participating' => false,
            ], 500);
        }
    }
}
