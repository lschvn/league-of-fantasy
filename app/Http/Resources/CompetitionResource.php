<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region' => $this->region,
            'season' => $this->season,
            'teams_count' => $this->whenCounted('teams'),
            'weeks_count' => $this->whenCounted('weeks'),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'weeks' => WeekResource::collection($this->whenLoaded('weeks')),
        ];
    }
}
