<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'competition_id' => $this->competition_id,
            'name' => $this->name,
            'tag' => $this->tag,
            'logo_url' => $this->logo_url,
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}
