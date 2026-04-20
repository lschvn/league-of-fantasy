<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'nickname',
        'role',
        'status',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(PlayerStat::class);
    }

    public function rosterSlots(): HasMany
    {
        return $this->hasMany(RosterSlot::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }
}
