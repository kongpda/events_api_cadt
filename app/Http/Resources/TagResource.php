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
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="name", type="string", example="Music Festival")
     * @OA\Property(property="slug", type="string", example="music-festival")
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-01T12:00:00")
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-02T14:30:00")
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'events' => EventResource::collection($this->whenLoaded('events')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
