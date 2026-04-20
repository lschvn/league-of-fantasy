<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class FantasyLeague extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'creator_user_id',
        'name',
        'visibility',
        'status',
        'max_participants',
        'budget_cap',
        'join_deadline',
        'scoring_rule_version',
    ];

    protected function casts(): array
    {
        return [
            'join_deadline' => 'datetime',
            'budget_cap' => 'decimal:2',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function fantasyTeams(): HasManyThrough
    {
        return $this->hasManyThrough(FantasyTeam::class, Membership::class, 'fantasy_league_id', 'membership_id');
    }
}
