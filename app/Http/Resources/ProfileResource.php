<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'profiles',
            'id' => (string) $this->id,
            'attributes' => [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'full_name' => $this->when(
                    $this->first_name || $this->last_name,
                    mb_trim($this->first_name . ' ' . $this->last_name)
                ),
                'birth_date' => $this->birth_date?->format('Y-m-d'),
                'phone' => $this->phone,
                'avatar' => $this->avatar ? url(Storage::url($this->avatar)) : null,
                'status' => $this->status,
                'bio' => $this->bio,
                'address' => $this->address,
                'social_links' => $this->social_links,
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
            'relationships' => [
                'user' => UserResource::make($this->whenLoaded('user')),
            ],
            'links' => [
                'self' => route('profiles.show', $this->id),
            ],
        ];
    }
}
