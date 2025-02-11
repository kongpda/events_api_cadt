<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     title="Tag",
 *     description="Tag resource"
 * )
 */
final class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'tags',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'is_active' => (bool) $this->is_active,
                'position' => (int) $this->position,
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
            'relationships' => [
                'events' => [
                    'data' => $this->when($this->relationLoaded('events'), fn () => $this->events->map(fn ($event) => [
                        'type' => 'events',
                        'id' => (string) $event->id,
                    ])->all()),
                ],
            ],
            'included' => [
                $this->when(
                    $this->relationLoaded('events'),
                    fn () => EventResource::collection($this->events)
                ),
            ],
            'links' => [
                'self' => route('tags.show', $this->resource),
            ],
        ];
    }
}
