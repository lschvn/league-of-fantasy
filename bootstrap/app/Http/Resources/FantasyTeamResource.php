<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FantasyTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membership_id' => $this->membership_id,
            'name' => $this->name,
            'budget_remaining' => (float) $this->budget_remaining,
            'membership' => new MembershipResource($this->whenLoaded('membership')),
            'roster' => RosterSlotResource::collection($this->whenLoaded('activeRosterSlots')),
        ];
    }
}
