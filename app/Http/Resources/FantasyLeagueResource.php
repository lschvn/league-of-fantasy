<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyLeagueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'competition_id' => $this->competition_id,
            'creator_user_id' => $this->creator_user_id,
            'name' => $this->name,
            'visibility' => $this->visibility,
            'status' => $this->status,
            'max_participants' => $this->max_participants,
            'budget_cap' => (float) $this->budget_cap,
            'join_deadline' => $this->join_deadline?->toISOString(),
            'scoring_rule_version' => $this->scoring_rule_version,
            'competition' => new CompetitionResource($this->whenLoaded('competition')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'memberships_count' => $this->whenCounted('memberships'),
            'fantasy_teams_count' => $this->when(isset($this->fantasy_teams_count), $this->fantasy_teams_count),
            'memberships' => MembershipResource::collection($this->whenLoaded('memberships')),
        ];
    }
}
