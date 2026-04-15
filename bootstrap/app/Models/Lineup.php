<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lineup extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_team_id',
        'week_id',
        'status',
        'submitted_at',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
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

    public function slots(): HasMany
    {
        return $this->hasMany(LineupSlot::class);
    }
}
