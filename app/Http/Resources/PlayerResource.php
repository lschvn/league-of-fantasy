<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'nickname' => $this->nickname,
            'role' => $this->role,
            'status' => $this->status,
            'team' => new TeamResource($this->whenLoaded('team')),
        ];
    }
}
