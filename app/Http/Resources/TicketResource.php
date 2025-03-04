<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'tickets',
            'id' => (string) $this->id,
            'attributes' => [
                'status' => $this->status,
                'purchase_date' => $this->purchase_date->toISOString(),
                'price' => $this->price,
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
                'qr_code_data' => $this->getQrCodeData(),
            ],
            'relationships' => [
                'events' => [
                    'data' => $this->when($this->relationLoaded('event'), fn () => [
                        'type' => 'events',
                        'id' => (string) $this->event->id,
                    ]),
                ],
                'user' => [
                    'data' => $this->when($this->relationLoaded('user'), fn () => [
                        'type' => 'users',
                        'id' => (string) $this->user->id,
                    ]),
                ],
                'ticket_types' => [
                    'data' => $this->when($this->relationLoaded('ticketType'), fn () => [
                        'type' => 'ticket_types',
                        'id' => (string) $this->ticketType->id,
                    ]),
                ],
            ],
            'included' => $this->when($request->includes ?? false, fn () => array_filter([
                $this->whenLoaded('event', fn () => new EventResource($this->event)),
                $this->whenLoaded('user', fn () => new UserResource($this->user)),
                $this->whenLoaded('ticketType', fn () => new TicketTypeResource($this->ticketType)),
            ])),
            'links' => [
                'self' => route('tickets.show', $this->resource),
                'event' => route('events.show', $this->event_id),
                'user' => route('users.show', $this->user_id),
                'ticket_type' => route('ticket_types.show', $this->ticket_type_id),
            ],
        ];
    }
}
