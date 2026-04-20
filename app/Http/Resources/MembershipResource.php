<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fantasy_league_id' => $this->fantasy_league_id,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'status' => $this->status,
            'joined_at' => $this->joined_at?->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'fantasy_team' => new FantasyTeamResource($this->whenLoaded('fantasyTeam')),
        ];
    }
}
