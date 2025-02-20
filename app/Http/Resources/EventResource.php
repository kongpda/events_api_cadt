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
            'type' => 'events',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'feature_image' => $this->feature_image_url,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                $this->mergeWhen($request->routeIs('events.show'), [
                    'slug' => $this->slug,
                    'description' => $this->description,
                    'location' => $this->location,
                    'participation_type' => $this->participation_type,
                    'capacity' => $this->capacity,
                    'registration_deadline' => $this->registration_deadline,
                    'registration_status' => $this->registration_status,
                    'event_type' => $this->event_type,
                    'status' => $this->status,
                    'online_url' => $this->online_url,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ]),
                'favorites_count' => $this->favorites_count,
                'is_favorited' => $request->user() ? $this->isFavoritedBy($request->user()) : false,
                'is_featured' => $this->whenLoaded('featuredEvent', fn () => true, false),
                'featured_order' => $this->whenLoaded('featuredEvent', fn () => $this->featuredEvent->order),
            ],
            'relationships' => [
                $this->mergeWhen($request->routeIs('events.show'), [
                    'category' => new CategoryResource($this->whenLoaded('category')),
                    'user' => $this->whenLoaded('user', fn () => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'profile' => [
                            'first_name' => $this->user->profile->first_name,
                            'last_name' => $this->user->profile->last_name,
                            'avatar' => $this->user->profile->avatar_url,
                        ],
                    ]),
                    'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
                    'tags' => TagResource::collection($this->whenLoaded('tags')),
                    'participants' => EventParticipantResource::collection($this->whenLoaded('participants')),
                ]),
            ],
            'links' => [
                'self' => route('events.show', $this->id),
                'toggleFavorite' => route('events.favorite', $this->id),
            ],
        ];
    }
}
