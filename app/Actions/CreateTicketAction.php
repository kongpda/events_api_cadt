<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ParticipationStatus;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class CreateTicketAction
{
    /**
     * Create a ticket for a user joining an event.
     *
     * @param  Event  $event  The event being joined
     * @param  User  $user  The user joining the event
     * @param  int|null  $ticketTypeId  Optional ticket type ID
     * @return Ticket The created ticket
     *
     * @throws ModelNotFoundException When specified ticket type is not found
     */
    public function execute(Event $event, User $user, ?int $ticketTypeId = null): Ticket
    {
        return DB::transaction(function () use ($event, $user, $ticketTypeId): Ticket {
            // Get the ticket type if provided
            $ticketType = null;
            $finalTicketTypeId = null;
            $price = 0;

            if ($ticketTypeId) {
                try {
                    $ticketType = TicketType::where('event_id', $event->id)
                        ->where('id', $ticketTypeId)
                        ->firstOrFail();

                    $finalTicketTypeId = $ticketType->id;
                    $price = $ticketType->price;
                } catch (ModelNotFoundException $e) {
                    Log::warning('Specified ticket type not found, creating ticket without ticket type', [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'ticket_type_id' => $ticketTypeId,
                    ]);
                    // We'll continue without a ticket type
                }
            } else {
                // Try to get the default ticket type (first one)
                $ticketType = TicketType::where('event_id', $event->id)->first();

                if ($ticketType) {
                    $finalTicketTypeId = $ticketType->id;
                    $price = $ticketType->price;
                } else {
                    Log::info('No ticket type found for event, creating ticket without ticket type', [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                    ]);
                    // We'll continue without a ticket type
                }
            }

            // Create and return the ticket
            return Ticket::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'ticket_type_id' => $finalTicketTypeId, // This can now be null
                'status' => ParticipationStatus::REGISTERED->value,
                'purchase_date' => now(),
                'price' => $price,
            ]);
        });
    }
}
