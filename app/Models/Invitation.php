<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_league_id',
        'code',
        'expires_at',
        'max_uses',
        'used_count',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function fantasyLeague(): BelongsTo
    {
        return $this->belongsTo(FantasyLeague::class);
    }

    // check if the invitation can still be used
    public function isValid(): bool
    {
        return is_null($this->revoked_at)
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && $this->used_count < $this->max_uses;
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }
}
