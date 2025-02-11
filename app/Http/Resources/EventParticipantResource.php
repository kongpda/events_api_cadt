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
                'ticket_id' => $this->ticket_id,
                'check_in_time' => $this->check_in_time,
                'joined_at' => $this->joined_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationship' => [
                $this->mergeWhen($request->routeIs('event_participants.show'), [
                    'event' => new EventResource($this->whenLoaded('event')),
                    'user' => new UserResource($this->whenLoaded('user')),
                    'ticket' => new TicketResource($this->whenLoaded('ticket')),
                ]),
            ],
        ];
    }
}
