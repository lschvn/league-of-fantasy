<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'pandascore_id',
        'name',
        'region',
        'season',
    ];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(Week::class);
    }

    public function fantasyLeagues(): HasMany
    {
        return $this->hasMany(FantasyLeague::class);
    }
}
