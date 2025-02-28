<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class EventParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'event_participants',
            'id' => $this->id,
            'attributes' => [
                'event_id' => $this->event_id,
                'user_id' => $this->user_id,
                'status' => $this->status->value,
                'participation_type' => $this->participation_type->value,
                'ticket_type_id' => $this->ticket_type_id,
                'check_in_time' => $this->check_in_time,
                'joined_at' => $this->joined_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'user' => [
                    'data' => $this->whenLoaded('user', fn () => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        // Add other user fields you want to include
                    ]),
                ],
                'ticket_type' => $this->whenLoaded('ticketType', fn () => [
                    'id' => $this->ticketType->id,
                    'name' => $this->ticketType->name,
                    // Add other ticket type fields you want to include
                ]),
            ],
        ];
    }
}
