<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteSoundscape extends Model
{
    protected $fillable = [
        'user_id',
        'soundscape_id'
    ];
}
