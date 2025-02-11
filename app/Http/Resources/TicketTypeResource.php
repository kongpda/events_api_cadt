<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketType
 */
final class TicketTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'ticket_type',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'description' => $this->description,
                'status' => $this->status,
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
            'relationships' => [
                'event' => $this->when($this->relationLoaded('event'), fn () => [
                    'type' => 'event',
                    'id' => (string) $this->event_id,
                ]),
                'creator' => $this->when($this->relationLoaded('creator'), fn () => [
                    'type' => 'user',
                    'id' => (string) $this->created_by,
                ]),
            ],
            'links' => [
                'self' => route('ticket_types.show', $this->id),
            ],
        ];
    }
}
