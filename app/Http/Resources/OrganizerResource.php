<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrganizerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'organizers',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'email' => $this->email,
                'phone' => $this->phone,
                $this->mergeWhen($request->routeIs('organizers.show'), [
                    'description' => $this->description,
                    'address' => $this->address,
                    'website' => $this->website,
                    'social_media' => $this->social_media,
                    'logo' => $this->logo,
                    'is_verified' => $this->is_verified,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ]),
                'events_count' => $this->events_count ?? 0,
                'upcoming_events_count' => $this->upcoming_events_count ?? 0,
                'past_events_count' => $this->past_events_count ?? 0,
            ],
            'relationships' => [
                $this->mergeWhen($request->routeIs('organizers.show'), [
                    'user' => new UserResource($this->whenLoaded('user')),
                    'events' => EventResource::collection($this->whenLoaded('events')),
                ]),
            ],
            'links' => [
                'self' => route('organizers.show', $this->id),
                'events' => route('organizers.show', [
                    'organizer' => $this->id,
                    'include' => 'events',
                ]),
            ],
        ];
    }
}
