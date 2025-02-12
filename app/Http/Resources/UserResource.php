<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'users',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'events' => [
                    $this->when($this->relationLoaded('events'), fn () => $this->events->map(fn ($event) => [
                        'type' => 'events',
                        'id' => $event->id,
                    ])->all()),
                ],
            ],
            'links' => [
                'self' => route('users.show', $this->id),
            ],
        ];
    }
}
