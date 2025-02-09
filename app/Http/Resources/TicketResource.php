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
            'id' => $this->id,
            'event' => new EventResource($this->whenLoaded('event')),
            'user' => new UserResource($this->whenLoaded('user')),
            'ticket_type' => new TicketTypeResource($this->whenLoaded('ticketType')),
            'status' => $this->status,
            'purchase_date' => $this->purchase_date->toISOString(),
            'price' => $this->price,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
