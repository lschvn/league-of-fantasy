<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Week extends Model
{
    protected $fillable = ['league_id','start_at','end_at',];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

}
