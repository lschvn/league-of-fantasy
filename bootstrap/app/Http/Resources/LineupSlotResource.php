<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LineupSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lineup_id' => $this->lineup_id,
            'roster_slot_id' => $this->roster_slot_id,
            'position' => $this->position,
            'is_captain' => (bool) $this->is_captain,
            'roster_slot' => new RosterSlotResource($this->whenLoaded('rosterSlot')),
        ];
    }
}
