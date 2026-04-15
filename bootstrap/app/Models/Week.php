<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Week extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'number',
        'start_at',
        'end_at',
        'lineup_lock_at',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'lineup_lock_at' => 'datetime',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'week_id');
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
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
