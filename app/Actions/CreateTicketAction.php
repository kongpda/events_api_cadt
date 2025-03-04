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

                    // Try to get any ticket type for this event
                    $ticketType = TicketType::where('event_id', $event->id)->first();

                    if ($ticketType) {
                        $finalTicketTypeId = $ticketType->id;
                        $price = $ticketType->price;
                    } else {
                        // Create a default ticket type
                        $ticketType = $this->createDefaultTicketType($event);
                        $finalTicketTypeId = $ticketType->id;
                    }
                }
            } else {
                // Try to get the default ticket type (first one)
                $ticketType = TicketType::where('event_id', $event->id)->first();

                if ($ticketType) {
                    $finalTicketTypeId = $ticketType->id;
                    $price = $ticketType->price;
                } else {
                    Log::info('No ticket type found for event, creating default ticket type', [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                    ]);

                    // Create a default ticket type
                    $ticketType = $this->createDefaultTicketType($event);
                    $finalTicketTypeId = $ticketType->id;
                }
            }

            // Create and return the ticket
            return Ticket::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'ticket_type_id' => $finalTicketTypeId,
                'status' => ParticipationStatus::REGISTERED->value,
                'purchase_date' => now(),
                'price' => $price,
            ]);
        });
    }

    /**
     * Create a default ticket type for an event.
     *
     * @param  Event  $event  The event to create a ticket type for
     * @return TicketType The created ticket type
     */
    private function createDefaultTicketType(Event $event): TicketType
    {
        // Create a free general admission ticket type
        $generalAdmission = TicketType::create([
            'event_id' => $event->id,
            'created_by' => $event->user_id,
            'name' => 'General Admission',
            'price' => 0,
            'quantity' => 0, // 0 for unlimited
            'description' => 'Standard entry to the event',
            'status' => 'active',
        ]);

        // Create a premium ticket type
        TicketType::create([
            'event_id' => $event->id,
            'created_by' => $event->user_id,
            'name' => 'Premium',
            'price' => 25.00,
            'quantity' => 50,
            'description' => 'Premium access with additional benefits',
            'status' => 'active',
        ]);

        // Create a VIP ticket type
        TicketType::create([
            'event_id' => $event->id,
            'created_by' => $event->user_id,
            'name' => 'VIP',
            'price' => 50.00,
            'quantity' => 20,
            'description' => 'VIP access with exclusive perks',
            'status' => 'active',
        ]);

        // Return the general admission ticket type as the default
        return $generalAdmission;
    }
}
