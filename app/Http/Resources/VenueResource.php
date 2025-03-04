<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Venue",
 *     title="Venue",
 *     description="Venue resource"
 * )
 */
final class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'venues',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'capacity' => $this->capacity,
                'created_at' => $this->created_at?->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
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
                'self' => '',
            ],
        ];
    }
}
