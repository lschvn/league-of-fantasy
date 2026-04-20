<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fantasy_league_id' => $this->fantasy_league_id,
            'week_id' => $this->week_id,
            'status' => $this->status,
            'start_at' => $this->start_at?->toISOString(),
            'end_at' => $this->end_at?->toISOString(),
            'is_open' => $this->isOpen(),
        ];
    }
}
