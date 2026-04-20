<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineupSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'lineup_id',
        'roster_slot_id',
        'position',
        'is_captain',
    ];

    protected function casts(): array
    {
        return [
            'is_captain' => 'boolean',
        ];
    }

    public function lineup(): BelongsTo
    {
        return $this->belongsTo(Lineup::class);
    }

    public function rosterSlot(): BelongsTo
    {
        return $this->belongsTo(RosterSlot::class);
    }
}
