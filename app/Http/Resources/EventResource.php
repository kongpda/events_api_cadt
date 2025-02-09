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
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'feature_image' => $this->feature_image,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user' => new UserResource($this->whenLoaded('user')),
            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'participation_type' => $this->participation_type,
            'capacity' => $this->capacity,
            'registration_deadline' => $this->registration_deadline,
            'registration_status' => $this->registration_status,
            'event_type' => $this->event_type,
            'online_url' => $this->online_url,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
