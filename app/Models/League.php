<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $fillable = ['name','region',];

    public function users()
    {
        return $this->hasMany(User::class);
    }

}
