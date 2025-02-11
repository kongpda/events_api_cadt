<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'event',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'feature_image' => $this->feature_image,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                $this->mergeWhen($request->is('api/events/{event}'), [
                    'slug' => $this->slug,
                    'description' => $this->description,
                    'address' => $this->address,
                    'participation_type' => $this->participation_type,
                    'capacity' => $this->capacity,
                    'registration_deadline' => $this->registration_deadline,
                    'registration_status' => $this->registration_status,
                    'event_type' => $this->event_type,
                    'online_url' => $this->online_url,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ]),
                'is_favorited' => $request->user() ? $this->isFavoritedBy($request->user()) : false,
            ],
            'relationships' => [
                $this->mergeWhen($request->is('api/events/{event}'), [
                    'category' => new CategoryResource($this->whenLoaded('category')),
                    'user' => new UserResource($this->whenLoaded('user')),
                    'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
                    'tags' => TagResource::collection($this->whenLoaded('tags')),
                ]),
            ],
            'links' => [
                'self' => route('events.show', $this->id),
                'toggleFavorite' => route('events.favorite', $this->id),
            ],
        ];
    }
}
