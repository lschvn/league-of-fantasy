<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'player_id',
        'kills',
        'deaths',
        'assists',
        'fantasy_points',
    ];

    protected function casts(): array
    {
        return [
            'fantasy_points' => 'decimal:2',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
