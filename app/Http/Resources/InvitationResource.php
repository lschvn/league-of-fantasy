<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fantasy_league_id' => $this->fantasy_league_id,
            'code' => $this->code,
            'expires_at' => $this->expires_at?->toISOString(),
            'max_uses' => $this->max_uses,
            'used_count' => $this->used_count,
            'revoked_at' => $this->revoked_at?->toISOString(),
            'is_valid' => $this->isValid(),
            'fantasy_league' => new FantasyLeagueResource($this->whenLoaded('fantasyLeague')),
        ];
    }
}
