<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Event",
 *     title="Event",
 *     description="Event resource"
 * )
 */
final class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="title", type="string", example="Conference")
     * @OA\Property(property="slug", type="string", example="annual-tech-conference")
     * @OA\Property(property="description", type="string", example="A detailed description of the conference")
     * @OA\Property(property="venue_id", type="integer", example=1)
     * @OA\Property(property="feature_image", type="string", example="conference.jpg")
     * @OA\Property(property="content", type="object", example={"description": "Annual tech conference", "agenda": "..."})
     * @OA\Property(property="event_date", type="object", example={"start": "2023-06-15T09:00:00", "end": "2023-06-17T18:00:00"})
     * @OA\Property(property="action_content", type="object", example={"cta": "Register Now", "url": "/register"})
     * @OA\Property(property="status", type="string", example="published")
     * @OA\Property(property="user_id", type="string", format="ulid", example="01HNY5EPCR3PXVK6QHGD5NKVP8")
     * @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category"))
     * @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/Tag"))
     * @OA\Property(property="venue", ref="#/components/schemas/Venue")
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-01T12:00:00")
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-02T14:30:00")
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'venue_id' => $this->venue_id,
            'feature_image' => $this->feature_image ? url('storage/' . $this->feature_image) : null,
            'content' => $this->content,
            'event_date' => $this->event_date,
            'action_content' => $this->action_content,
            'status' => $this->status,
            'user_id' => $this->user_id,

            // Relationships
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'venue' => new VenueResource($this->whenLoaded('venue')),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
