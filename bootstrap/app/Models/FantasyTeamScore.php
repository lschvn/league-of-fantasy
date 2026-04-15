<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FantasyTeamScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_team_id',
        'week_id',
        'points',
        'rank',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function fantasyTeam(): BelongsTo
    {
        return $this->belongsTo(FantasyTeam::class);
    }

    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }
}
