<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteSoundscape extends Model
{
    protected $table = 'user_favorite_soundscape';
    
    protected $fillable = [
        'user_id',
        'soundscape_id'
    ];
}
