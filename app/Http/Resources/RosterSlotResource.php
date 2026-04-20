<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RosterSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fantasy_team_id' => $this->fantasy_team_id,
            'player_id' => $this->player_id,
            'acquisition_cost' => (float) $this->acquisition_cost,
            'acquired_at' => $this->acquired_at?->toISOString(),
            'released_at' => $this->released_at?->toISOString(),
            'status' => $this->status,
            'player' => new PlayerResource($this->whenLoaded('player')),
        ];
    }
}
