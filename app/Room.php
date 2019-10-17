<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
         'title','description', 'price'
    ];
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
