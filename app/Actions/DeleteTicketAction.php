<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class DeleteTicketAction
{
    /**
     * Delete tickets for a user leaving an event.
     *
     * @param  Event  $event  The event being left
     * @param  User  $user  The user leaving the event
     * @return bool Whether the deletion was successful
     */
    public function execute(Event $event, User $user): bool
    {
        try {
            return DB::transaction(function () use ($event, $user): bool {
                // Find all tickets for this user and event
                $tickets = Ticket::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->get();

                if ($tickets->isEmpty()) {
                    // No tickets found, but we'll consider this a success
                    // since the end result is what was desired (no tickets)
                    return true;
                }

                // Delete all tickets
                foreach ($tickets as $ticket) {
                    $ticket->delete();
                }

                return true;
            });
        } catch (Exception $e) {
            // Log the error but don't throw it up the chain
            // This ensures the participant can still be removed even if ticket deletion fails
            Log::error('Failed to delete tickets for event: ' . $event->id . ', user: ' . $user->id, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
