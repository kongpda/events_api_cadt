<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'categories',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                $this->mergeWhen($request->routeIs('categories.show'), [
                    'description' => $this->description,
                    'is_active' => (bool) $this->is_active,
                    'position' => (int) $this->position,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ]),
            ],
            'relationships' => [
                'events' => $this->when(
                    $request->routeIs('categories.show') && $this->relationLoaded('events'),
                    fn () => EventResource::collection($this->events)
                ),
            ],
            'links' => [
                'self' => route('categories.show', $this->slug),
            ],
        ];
    }
}
