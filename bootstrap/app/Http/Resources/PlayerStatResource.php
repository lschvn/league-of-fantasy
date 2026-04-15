<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerStatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'match_id' => $this->match_id,
            'player_id' => $this->player_id,
            'kills' => $this->kills,
            'deaths' => $this->deaths,
            'assists' => $this->assists,
            'fantasy_points' => (float) $this->fantasy_points,
            'player' => new PlayerResource($this->whenLoaded('player')),
        ];
    }
}
