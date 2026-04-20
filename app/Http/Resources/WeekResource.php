<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeekResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'competition_id' => $this->competition_id,
            'number' => $this->number,
            'start_at' => $this->start_at?->toISOString(),
            'end_at' => $this->end_at?->toISOString(),
            'lineup_lock_at' => $this->lineup_lock_at?->toISOString(),
            'matches_count' => $this->whenCounted('matches'),
            'matches' => GameMatchResource::collection($this->whenLoaded('matches')),
        ];
    }
}
