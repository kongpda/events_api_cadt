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
     * @OA\Property(property="venue_id", type="integer", example=1)
     * @OA\Property(property="category_id", type="integer", example=2)
     * @OA\Property(property="tag_id", type="integer", example=3)
     * @OA\Property(property="feature_image", type="string", example="conference.jpg")
     * @OA\Property(property="content", type="object", example={"description": "Annual tech conference", "agenda": "..."})
     * @OA\Property(property="event_date", type="object", example={"start": "2023-06-15T09:00:00", "end": "2023-06-17T18:00:00"})
     * @OA\Property(property="action_content", type="object", example={"cta": "Register Now", "url": "/register"})
     * @OA\Property(property="user_id", type="integer", example=1)
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-01T12:00:00")
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-02T14:30:00")
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'venue_id' => $this->venue_id,
            'category_id' => $this->category_id,
            'tag_id' => $this->tag_id,
            'feature_image' => $this->feature_image ? url('storage/' . $this->feature_image) : null,
            'content' => $this->content,
            'event_date' => $this->event_date,
            'action_content' => $this->action_content,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
