<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LineupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fantasy_team_id' => $this->fantasy_team_id,
            'week_id' => $this->week_id,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'locked_at' => $this->locked_at?->toISOString(),
            'slots' => LineupSlotResource::collection($this->whenLoaded('slots')),
        ];
    }
}
