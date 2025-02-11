<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ShareResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'shares',
            'id' => (string) $this->id,
            'attributes' => [
                'platform' => $this->platform,
                'share_url' => $this->share_url,
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
            'relationships' => [
                $this->mergeWhen($request->routeIs('shares.show'), [
                    'user' => new UserResource($this->whenLoaded('user')),
                    'event' => EventResource::collection($this->whenLoaded('events')),
                ]),
            ],
            'included' => [
                $this->when($this->relationLoaded('user'), new UserResource($this->user)),
                $this->when($this->relationLoaded('event'), new EventResource($this->event)),
            ],
            'links' => [
                'self' => route('shares.show', $this->id),
            ],
        ];
    }
}
