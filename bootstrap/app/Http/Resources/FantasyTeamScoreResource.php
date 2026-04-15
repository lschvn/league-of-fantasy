<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyTeamScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fantasy_team_id' => $this->fantasy_team_id,
            'week_id' => $this->week_id,
            'points' => (float) $this->points,
            'rank' => $this->rank,
            'calculated_at' => $this->calculated_at?->toISOString(),
            'fantasy_team' => new FantasyTeamResource($this->whenLoaded('fantasyTeam')),
        ];
    }
}
