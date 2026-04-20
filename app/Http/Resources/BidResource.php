<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'auction_id' => $this->auction_id,
            'fantasy_team_id' => $this->fantasy_team_id,
            'player_id' => $this->player_id,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'placed_at' => $this->placed_at?->toISOString(),
            'player' => new PlayerResource($this->whenLoaded('player')),
        ];
    }
}
