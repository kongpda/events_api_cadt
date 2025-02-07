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
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="name", type="string", example="Grand Hall")
     * @OA\Property(property="address", type="string", example="123 Main Street")
     * @OA\Property(property="city", type="string", example="Phnom Penh")
     * @OA\Property(property="state", type="string", example="Phnom Penh")
     * @OA\Property(property="country", type="string", example="Cambodia")
     * @OA\Property(property="postal_code", type="string", example="12000")
     * @OA\Property(property="capacity", type="integer", example=500)
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-01T12:00:00")
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-05-02T14:30:00")
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'capacity' => $this->capacity,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
