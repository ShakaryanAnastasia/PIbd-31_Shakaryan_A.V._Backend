<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
         'description'
    ];
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
