<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class FantasyTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'name',
        'budget_remaining',
    ];

    protected function casts(): array
    {
        return [
            'budget_remaining' => 'decimal:2',
        ];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function fantasyLeague(): HasOneThrough
    {
        return $this->hasOneThrough(
            FantasyLeague::class,
            Membership::class,
            'id',
            'id',
            'membership_id',
            'fantasy_league_id'
        );
    }

    public function rosterSlots(): HasMany
    {
        return $this->hasMany(RosterSlot::class);
    }

    public function activeRosterSlots(): HasMany
    {
        return $this->hasMany(RosterSlot::class)->where('status', 'active');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(Lineup::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(FantasyTeamScore::class);
    }
}
