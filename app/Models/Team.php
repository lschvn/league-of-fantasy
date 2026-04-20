<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['league_id','name','tag','logo_url',];

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
